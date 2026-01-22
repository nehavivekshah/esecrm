<?php

namespace App\Http\Controllers;

use App\Models\Leads;
use App\Models\Invoices;
use App\Models\Proposals;
use App\Models\Clients;
use App\Models\Task;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportingApiController extends Controller
{
    /**
     * Get sales pipeline report
     */
    public function salesPipeline(Request $request)
    {
        $company_id = $request->company_id;
        $query = Leads::query();

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        $pipeline = [
            'new' => $query->clone()->where('status', 'new')->count(),
            'qualified' => $query->clone()->where('status', 'qualified')->count(),
            'negotiating' => $query->clone()->where('status', 'negotiating')->count(),
            'won' => $query->clone()->where('status', 'won')->count(),
            'lost' => $query->clone()->where('status', 'lost')->count(),
        ];

        $leadValues = [
            'new' => $query->clone()->where('status', 'new')->sum('values'),
            'qualified' => $query->clone()->where('status', 'qualified')->sum('values'),
            'negotiating' => $query->clone()->where('status', 'negotiating')->sum('values'),
            'won' => $query->clone()->where('status', 'won')->sum('values'),
            'lost' => $query->clone()->where('status', 'lost')->sum('values'),
        ];

        $conversionRate = $query->count() > 0 
            ? round(($query->clone()->where('status', 'won')->count() / $query->count()) * 100, 2)
            : 0;

        return response()->json([
            'message' => 'Sales pipeline report retrieved',
            'data' => [
                'lead_counts' => $pipeline,
                'lead_values' => $leadValues,
                'total_leads' => $query->count(),
                'total_value' => $query->sum('values'),
                'conversion_rate' => $conversionRate,
            ],
        ]);
    }

    /**
     * Get revenue report
     */
    public function revenue(Request $request)
    {
        $from_date = $request->from_date ?? Carbon::now()->startOfMonth();
        $to_date = $request->to_date ?? Carbon::now()->endOfMonth();
        $company_id = $request->company_id;

        $query = Invoices::whereBetween('invoice_date', [$from_date, $to_date]);

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        $invoiceData = (clone $query)->with('client')->get();

        $revenue = [
            'total_invoices' => $query->count(),
            'total_value' => $query->sum('total'),
            'paid_value' => (clone $query)->where('status', 'paid')->sum('total'),
            'pending_value' => (clone $query)->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total'),
            'paid_invoices' => (clone $query)->where('status', 'paid')->count(),
            'pending_invoices' => (clone $query)->whereIn('status', ['draft', 'sent', 'overdue'])->count(),
            'avg_invoice_value' => $query->count() > 0 ? round($query->avg('total'), 2) : 0,
            'payment_percentage' => $query->count() > 0
                ? round(((clone $query)->where('status', 'paid')->sum('total') / $query->sum('total')) * 100, 2)
                : 0,
        ];

        // Daily breakdown
        $dailyRevenue = (clone $query)->selectRaw('DATE(invoice_date) as date, COUNT(*) as count, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'message' => 'Revenue report retrieved',
            'data' => [
                'summary' => $revenue,
                'daily_breakdown' => $dailyRevenue,
            ],
        ]);
    }

    /**
     * Get top clients report
     */
    public function topClients(Request $request)
    {
        $limit = $request->limit ?? 10;
        $company_id = $request->company_id;

        $query = Clients::query();

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        $topClients = $query->with(['invoices'])
            ->get()
            ->map(function ($client) {
                return [
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'total_invoices' => $client->invoices->count(),
                    'total_value' => $client->invoices->sum('total'),
                    'paid_value' => $client->invoices->where('status', 'paid')->sum('total'),
                    'pending_value' => $client->invoices->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total'),
                ];
            })
            ->sortByDesc('total_value')
            ->take($limit);

        return response()->json([
            'message' => 'Top clients report retrieved',
            'data' => $topClients->values(),
        ]);
    }

    /**
     * Get team performance report
     */
    public function teamPerformance(Request $request)
    {
        $company_id = $request->company_id;
        $query = User::query();

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        $teamPerformance = $query->with(['assignedLeads', 'assignedTasks'])
            ->get()
            ->map(function ($user) {
                $leads = $user->assignedLeads;
                $tasks = $user->assignedTasks;

                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'role' => $user->role?->display_name ?? 'No Role',
                    'assigned_leads' => $leads->count(),
                    'won_leads' => $leads->where('status', 'won')->count(),
                    'lead_conversion' => $leads->count() > 0
                        ? round(($leads->where('status', 'won')->count() / $leads->count()) * 100, 2)
                        : 0,
                    'assigned_tasks' => $tasks->count(),
                    'completed_tasks' => $tasks->where('status', 'completed')->count(),
                    'task_completion' => $tasks->count() > 0
                        ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 2)
                        : 0,
                    'overdue_tasks' => $tasks->where('due_date', '<', now())
                        ->where('status', '!=', 'completed')->count(),
                ];
            })
            ->sortByDesc('lead_conversion');

        return response()->json([
            'message' => 'Team performance report retrieved',
            'data' => $teamPerformance->values(),
        ]);
    }

    /**
     * Get sales forecast
     */
    public function forecast(Request $request)
    {
        $months = $request->months ?? 3;
        $company_id = $request->company_id;

        $query = Leads::query();

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        $leads = $query->get();
        $conversionRate = $leads->count() > 0
            ? $leads->where('status', 'won')->count() / $leads->count()
            : 0;

        $avgLeadValue = $leads->count() > 0
            ? $leads->sum('values') / $leads->count()
            : 0;

        $forecast = [];
        $baseDate = Carbon::now();

        for ($i = 1; $i <= $months; $i++) {
            $date = $baseDate->clone()->addMonths($i);
            $monthLeads = $query->clone()->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)->count() ?? 10;

            $forecast[] = [
                'month' => $date->format('Y-m'),
                'projected_leads' => $monthLeads,
                'projected_won' => round($monthLeads * $conversionRate),
                'projected_revenue' => round($monthLeads * $conversionRate * $avgLeadValue, 2),
            ];
        }

        return response()->json([
            'message' => 'Sales forecast retrieved',
            'data' => [
                'conversion_rate' => round($conversionRate * 100, 2),
                'avg_lead_value' => round($avgLeadValue, 2),
                'forecast' => $forecast,
            ],
        ]);
    }

    /**
     * Get activity log report
     */
    public function activityLog(Request $request)
    {
        $from_date = $request->from_date ?? Carbon::now()->startOfDay();
        $to_date = $request->to_date ?? Carbon::now()->endOfDay();

        $query = Activity::whereBetween('created_at', [$from_date, $to_date]);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $activities = $query->with('user')
            ->latest()
            ->paginate($request->per_page ?? 30);

        $summary = [
            'total_activities' => (clone $query)->count(),
            'created_count' => (clone $query)->where('action', 'created')->count(),
            'updated_count' => (clone $query)->where('action', 'updated')->count(),
            'deleted_count' => (clone $query)->where('action', 'deleted')->count(),
        ];

        return response()->json([
            'message' => 'Activity log retrieved',
            'summary' => $summary,
            'data' => $activities,
        ]);
    }

    /**
     * Get dashboard summary
     */
    public function dashboard(Request $request)
    {
        $company_id = $request->company_id ?? auth()->user()->company_id;

        // Sales metrics
        $leads = Leads::where('company_id', $company_id)->get();
        $clients = Clients::where('company_id', $company_id)->count();
        $invoices = Invoices::where('company_id', $company_id)->get();
        $tasks = Task::whereIn('project_id', function ($q) use ($company_id) {
            $q->select('id')->from('projects')->where('company_id', $company_id);
        })->get();

        $dashboard = [
            'total_leads' => $leads->count(),
            'leads_this_month' => $leads->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'conversion_rate' => $leads->count() > 0
                ? round(($leads->where('status', 'won')->count() / $leads->count()) * 100, 2)
                : 0,
            'total_clients' => $clients,
            'total_invoices' => $invoices->count(),
            'total_revenue' => $invoices->sum('total'),
            'paid_revenue' => $invoices->where('status', 'paid')->sum('total'),
            'pending_revenue' => $invoices->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total'),
            'active_tasks' => $tasks->where('status', 'in_progress')->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'overdue_tasks' => $tasks->where('due_date', '<', now())
                ->where('status', '!=', 'completed')->count(),
            'pipeline_value' => [
                'new' => $leads->where('status', 'new')->sum('values'),
                'qualified' => $leads->where('status', 'qualified')->sum('values'),
                'negotiating' => $leads->where('status', 'negotiating')->sum('values'),
                'won' => $leads->where('status', 'won')->sum('values'),
            ],
        ];

        return response()->json([
            'message' => 'Dashboard summary retrieved',
            'data' => $dashboard,
        ]);
    }

    /**
     * Get custom report (generalized)
     */
    public function custom(Request $request)
    {
        $entity = $request->entity; // 'lead', 'invoice', 'proposal', 'client'
        $filters = $request->filters ?? [];
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $query = match($entity) {
            'lead' => Leads::query(),
            'invoice' => Invoices::query(),
            'proposal' => Proposals::query(),
            'client' => Clients::query(),
            default => null,
        };

        if (!$query) {
            return response()->json(['message' => 'Invalid entity type'], 400);
        }

        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $query->where($key, $value);
            }
        }

        // Apply date range
        if ($from_date && $to_date) {
            $query->whereBetween('created_at', [$from_date, $to_date]);
        }

        $data = $query->latest()->limit(1000)->get();

        return response()->json([
            'message' => 'Custom report generated',
            'entity' => $entity,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
