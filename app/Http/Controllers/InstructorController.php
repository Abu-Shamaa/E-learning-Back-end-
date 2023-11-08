<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Mail\InstructorCreation;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InstructorController extends Controller
{
    public function insIndex()
    {
        $instructor = User::join('assigned_roles', 'assigned_roles.entity_id', 'users.id')
            ->join('roles', 'assigned_roles.role_id', 'roles.id')
            ->where('roles.name', '=', 'ar_instructor')
            ->get(['users.id', 'users.name', 'users.email']);
        return response()->json($instructor);
    }


    public function insStore()
    {


        $form = request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',

        ]);
        $randpass = Str::random(16);
        $form['password'] = Hash::make($randpass);
        $form['is_instructor'] = TRUE;

        $data = [
            'name' => $form['name'],
            'username' =>  $form['email'],
            'password' => $randpass,
        ];

        DB::transaction(function () use ($form, $data) {
            $instructor = User::create($form);
            $instructor->assign('ar_instructor');
            // Send Email
            Mail::to($form['email'])->send(new InstructorCreation($data));
        });
        return response()->json([
            'status' => true,
            'message' => 'Instructor Created Successfully'

        ], 200);
    }

    public function insUpdate($id)
    {


        $inst = User::findOrFail($id);
        $form = request()->validate([
            'name' => 'string',
            //'email' => 'email',

        ]);


        $inst->update($form);
        return response()->json([
            'status' => true,
            'message' => 'Instructor updated Successfully'

        ], 200);
    }

    public function insDestroy($id)
    {
        $instructor = User::findOrFail($id);
        $instructor->delete();

        return response()->json([
            'status' => true,
            'message' => 'Instructor deleted Successfully'

        ], 200);
    }
}
