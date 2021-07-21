<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        // Role::create(['name' => 'admin']);
        // Role::create(['name' => 'staff']);
        // Role::create(['name' => 'user']);
        // Role::create(['name' => 'member']);

        // $all_roles_in_database = Role::all()->pluck('name');

        // dump($all_roles_in_database);

    }
}
