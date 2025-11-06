<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;



class UserController extends Controller
{
    


 public function index()
{
    $allusers = User::select('id','name','email','role_type','created_at')
        ->orderBy('id','desc')->get();

      

    return Inertia::render('Users/Index', [
        'allusers'   => $allusers,
        'totalUsers' => $allusers->count(),
    ]);
}


    // POST /users
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','email','max:255','unique:users,email'],
            'role_type' => ['required', Rule::in(['Admin','Cashier','Manager'])],
            'password'  => ['required','string','min:8'], // if you want auto-gen, you can make this nullable and set default
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    // PUT/PATCH /users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role_type' => ['required', Rule::in(['Admin','Cashier','Manager'])],
            'password'  => ['nullable','string','min:8'], // optional on update
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

   

 public function destroy(User $user)
    {

    

        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }
        $user->delete();
        return redirect()->route('users.index')->banner('User Deleted successfully.');
    }



}
