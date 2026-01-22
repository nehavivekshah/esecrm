<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

use App\Models\Companies;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Proposals;
use App\Models\Proposal_items;
use App\Models\Task;
use App\Models\Recoveries;
use App\Models\Projects;
use App\Models\Invoices;
use App\Models\Eselicenses;
use App\Models\Contracts;

class AjaxController extends Controller
{
    public function task()
    {
        return view('task');
    }

    public function tasksubmit(Request $request)
    {
        $tasks = Task::find($request->taskid);
        $tasks->name = $request->tasktitle;
        
        $tasks->update();
        
        //return redirect()->back()->with('status','Student Updated Successfully');

        return response(['success' => 'Updated.']);
    }
    
    public function ajaxSend(Request $request)
    {
        // Get the row ID from the request, or set it as an empty string if not present
        $id = $request->rowid ?? '';
    
        // Check if the userDelete action is requested
        if (($request->userDelete ?? '') == 'userDelete') {
            // Find the user by ID
            $user = User::find($id);
    
            // Check if the user exists
            if ($user) {
                // Delete the user
                $user->delete();
                return response()->json(['success' => 'User deleted successfully.']);
            } else {
                return response()->json(['error' => 'User not found.'], 404);
            }
        }elseif (($request->leadDelete ?? '') == 'leadDelete') {
            // Find the user by ID
            $leads = Leads::find($id);
    
            // Check if the user exists
            if ($leads) {
                $leads->delete();
                return response()->json(['success' => 'Leads deleted successfully.']);
            } else {
                return response()->json(['error' => 'Leads not found.'], 404);
            }
        }elseif (($request->contractDelete ?? '') == 'contractDelete') {
            // Find the user by ID
            $contracts = Contracts::find($id);
    
            // Check if the user exists
            if ($contracts) {
                $contracts->delete();
                return response()->json(['success' => 'Contract deleted successfully.']);
            } else {
                return response()->json(['error' => 'Contract not found.'], 404);
            }
        }elseif (($request->clientDelete ?? '') == 'clientDelete') {
            // Find the user by ID
            $client = Clients::find($id);
    
            // Check if the user exists
            if ($client) {
                
                Projects::where('client_id','=',$id)->delete();
                Recoveries::where('client_id','=',$id)->delete();
                // Delete the company
                $client->delete();
                
                return response()->json(['success' => 'Client deleted successfully.']);
            } else {
                return response()->json(['error' => 'Client not found.'], 404);
            }
        }elseif (($request->companyDelete ?? '') == 'companyDelete') {
            // Find the user by ID
            $company = Companies::find($id);
    
            // Check if the user exists
            if ($company) {
                User::where('cid','=',$id)->delete();
                // Delete the company
                $company->delete();
                return response()->json(['success' => 'Company deleted successfully.']);
            } else {
                return response()->json(['error' => 'Company not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'companyDeactivate') {
            // Find the user by ID
            $company = Companies::find($id);
            
            $company->status = 0;
            $company->update();
            
            // Check if the user exists
            if ($company) {
                return response()->json(['success' => 'Company Deactivated successfully.']);
            } else {
                return response()->json(['error' => 'Company not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'companyActivate') {
            // Find the user by ID
            $company = Companies::find($id);
            $company->status = 1;
            $company->update();
            
            // Check if the user exists
            if ($company) {
                return response()->json(['success' => 'Company Activated successfully.']);
            } else {
                return response()->json(['error' => 'Company not found.'], 404);
            }
        }elseif (($request->licenseDelete ?? '') == 'licenseDelete') {
            // Find the user by ID
            $eselicenses = Eselicenses::find($id);
    
            // Check if the user exists
            if ($eselicenses) {
                // Delete the company
                $eselicenses->delete();
                return response()->json(['success' => 'License deleted successfully.']);
            } else {
                return response()->json(['error' => 'License not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'licenseDeactivate') {
            // Find the user by ID
            $eselicenses = Eselicenses::find($id);
            
            $eselicenses->status = 'blocked';
            $eselicenses->update();
            
            // Check if the user exists
            if ($eselicenses) {
                return response()->json(['success' => 'License Deactivated successfully.']);
            } else {
                return response()->json(['error' => 'License not found.'], 404);
            }
        }elseif (($request->pagename ?? '') == 'licenseActivate') {
            // Find the user by ID
            $eselicenses = Eselicenses::find($id);
            $eselicenses->status = 'active';
            $eselicenses->update();
            
            // Check if the user exists
            if ($eselicenses) {
                return response()->json(['success' => 'License Activated successfully.']);
            } else {
                return response()->json(['error' => 'License not found.'], 404);
            }
        }elseif (($request->proposalDelete ?? '') == 'proposalDelete') {
            // Find the proposal by ID
            $proposal = Proposals::find($id);
    
            // Check if the proposal exists
            if ($proposal) {
                // Delete all related items first
                // If the relationship is defined, you can do:
                Proposal_items::where('proposal_id',$id)->delete();
    
                // Now, delete the proposal
                $proposal->delete();
                
                return response()->json(['success' => 'Proposal and its items were deleted successfully.']);
            } else {
                return response()->json(['error' => 'Proposal not found.'], 404);
            }
        }elseif (($request->recoveryAmountDelete ?? '') == 'recoveryAmountDelete') {
            // Find the user by ID
            $recovery = Recoveries::find($id);
    
            // Check if the user exists
            if ($recovery) {
                
                $project_id = $recovery->project_id ?? '';
                $project_paid = $recovery->paid ?? 0;
                
                // Delete the user
                //$recovery->delete();
                if($recovery->delete()){
                    // $project = Projects::find($project_id);
                    // $project->amount = (($project->amount ?? 0)+$project_paid);
                    // $project->update();
                    
                    return response()->json(['success' => 'User deleted successfully.']);
                }else{
                    return response()->json(['error' => 'Opps somethings went worng.'], 500);
                }
            } else {
                return response()->json(['error' => 'Recovery not found.'], 404);
            }
        } else {
            // Handle other operations here, if needed
            // For example, you can add more actions based on request parameters.
            // Update user, change status, etc.
    
            // Placeholder response for no delete action
            return response()->json(['success' => 'No delete action performed, but request processed successfully.']);
        }
    }
    
    public function taskSearch(Request $request)
    {
        $task = Task::leftjoin('users','tasks.uid','=','users.id')
        ->select('users.name','tasks.*')
        ->where('tasks.cid','=',Auth::user()->cid)
        ->where('tasks.title','LIKE',($request->updatedPositions ?? '').'%')->get();
        
        $output = '';
        foreach($task as $taskData){
        $output .= '<li><a href="/edit-task?id='.$taskData->id.'">'.$taskData->title.'<span>'.$taskData->name.'</span></a></li>';
        }
        
        return response()->json(['result' => $output]);
    }
}
