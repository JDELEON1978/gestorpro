<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $workspaces = $user->workspaces()
            ->orderBy('name')
            ->get(['workspaces.id','workspaces.name']);

        return view('dashboard', compact('workspaces'));
    }
}
