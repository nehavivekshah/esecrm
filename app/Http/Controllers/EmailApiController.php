<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\ScheduledEmail;
use App\Mail\CustomMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailApiController extends Controller
{
    /**
     * Get all email templates
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('active')) {
            $query->where('active', $request->active == 'true');
        }

        $templates = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Email templates retrieved successfully',
            'data' => $templates,
        ]);
    }

    /**
     * Create email template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:email_templates|regex:/^[a-z0-9_-]+$/',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'category' => 'nullable|string|max:100',
            'active' => 'nullable|boolean',
        ]);

        $template = EmailTemplate::create($validated);

        return response()->json([
            'message' => 'Email template created successfully',
            'data' => $template,
        ], 201);
    }

    /**
     * Get single template
     */
    public function show($id)
    {
        $template = EmailTemplate::findOrFail($id);

        return response()->json([
            'message' => 'Email template retrieved successfully',
            'data' => $template,
        ]);
    }

    /**
     * Update email template
     */
    public function update(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'subject' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'variables' => 'sometimes|array',
            'category' => 'sometimes|string|max:100',
            'active' => 'sometimes|boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'message' => 'Email template updated successfully',
            'data' => $template,
        ]);
    }

    /**
     * Delete email template
     */
    public function destroy($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return response()->json([
            'message' => 'Email template deleted successfully',
        ]);
    }

    /**
     * Send email immediately
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:email_templates,id',
            'recipient_email' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'variables' => 'nullable|array',
        ]);

        $subject = $validated['subject'];
        $body = $validated['body'];

        // Replace variables if provided
        if (!empty($validated['variables'])) {
            foreach ($validated['variables'] as $key => $value) {
                $subject = str_replace('{{' . $key . '}}', $value, $subject);
                $body = str_replace('{{' . $key . '}}', $value, $body);
            }
        }

        try {
            Mail::to($validated['recipient_email'])->send(
                new CustomMailable($subject, $body)
            );

            return response()->json([
                'message' => 'Email sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Schedule email for later
     */
    public function schedule(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:email_templates,id',
            'recipient_email' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'scheduled_at' => 'required|date|after:now',
            'variables' => 'nullable|array',
            'related_model_type' => 'nullable|string',
            'related_model_id' => 'nullable|integer',
        ]);

        $subject = $validated['subject'];
        $body = $validated['body'];

        // Replace variables if provided
        if (!empty($validated['variables'])) {
            foreach ($validated['variables'] as $key => $value) {
                $subject = str_replace('{{' . $key . '}}', $value, $subject);
                $body = str_replace('{{' . $key . '}}', $value, $body);
            }
        }

        $scheduled = ScheduledEmail::create([
            'recipient_email' => $validated['recipient_email'],
            'subject' => $subject,
            'body' => $body,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'pending',
            'related_model_type' => $validated['related_model_type'] ?? null,
            'related_model_id' => $validated['related_model_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Email scheduled successfully',
            'data' => $scheduled,
        ], 201);
    }

    /**
     * Get scheduled emails
     */
    public function scheduled(Request $request)
    {
        $query = ScheduledEmail::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $emails = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Scheduled emails retrieved',
            'data' => $emails,
        ]);
    }

    /**
     * Get email statistics
     */
    public function statistics()
    {
        $stats = [
            'total_templates' => EmailTemplate::count(),
            'active_templates' => EmailTemplate::where('active', true)->count(),
            'scheduled_emails' => ScheduledEmail::where('status', 'pending')->count(),
            'sent_emails' => ScheduledEmail::where('status', 'sent')->count(),
            'failed_emails' => ScheduledEmail::where('status', 'failed')->count(),
            'templates_by_category' => EmailTemplate::groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->get(),
        ];

        return response()->json([
            'message' => 'Email statistics retrieved',
            'data' => $stats,
        ]);
    }

    /**
     * Send test email
     */
    public function test(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'recipient_email' => 'required|email',
        ]);

        $template = EmailTemplate::findOrFail($validated['template_id']);
        $rendered = $template->render([]);

        try {
            Mail::to($validated['recipient_email'])->send(
                new CustomMailable($rendered['subject'], $rendered['body'])
            );

            return response()->json([
                'message' => 'Test email sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send test email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Render template with variables
     */
    public function render(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'variables' => 'nullable|array',
        ]);

        $rendered = $template->render($validated['variables'] ?? []);

        return response()->json([
            'message' => 'Template rendered successfully',
            'data' => $rendered,
        ]);
    }
}
