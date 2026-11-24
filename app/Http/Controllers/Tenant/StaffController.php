<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Display all store staff, managers and cashiers.
     */
    public function index(): View
    {
        $staffMembers = TenantUser::orderBy('role', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return view('tenant.staff.index', compact('staffMembers'));
    }

    /**
     * Create a new staff/cashier account.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:tenant_users,phone',
            'email' => 'nullable|email|max:100|unique:tenant_users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:manager,cashier',
        ], [
            'phone.unique' => 'এই মোবাইল নম্বর দিয়ে ইতোমধ্যে একটি অ্যাকাউন্ট আছে।',
            'email.unique' => 'এই ইমেইল ইতোমধ্যে ব্যবহৃত হয়েছে।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
        ]);

        TenantUser::create([
            'name' => trim($request->input('name')),
            'phone' => trim($request->input('phone')),
            'email' => $request->filled('email') ? trim($request->input('email')) : null,
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'is_active' => true,
        ]);

        return redirect()->route('tenant.staff.index')
            ->with('success', 'নতুন স্টাফ/ক্যাশিয়ার সফলভাবে যুক্ত হয়েছে!');
    }

    /**
     * Update staff info or reset password.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $staff = TenantUser::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => "required|string|max:20|unique:tenant_users,phone,{$id}",
            'email' => "nullable|email|max:100|unique:tenant_users,email,{$id}",
            'role' => 'required|in:owner,manager,cashier',
            'password' => 'nullable|string|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name' => trim($request->input('name')),
            'phone' => trim($request->input('phone')),
            'email' => $request->filled('email') ? trim($request->input('email')) : null,
            'role' => $request->input('role'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $staff->update($data);

        return redirect()->route('tenant.staff.index')
            ->with('success', "‘{$staff->name}’ এর তথ্য সফলভাবে আপডেট হয়েছে!");
    }

    /**
     * Delete a staff account.
     */
    public function destroy(int $id): RedirectResponse
    {
        $staff = TenantUser::findOrFail($id);

        if ($staff->role === 'owner') {
            return back()->with('error', 'দোকানের মূল মালিক (Owner) অ্যাকাউন্ট মুছে ফেলা সম্ভব নয়!');
        }

        if (Auth::guard('tenant_web')->id() === $staff->id) {
            return back()->with('error', 'আপনি নিজের অ্যাকাউন্ট নিজে মুছে ফেলতে পারবেন না!');
        }

        $name = $staff->name;
        $staff->delete();

        return redirect()->route('tenant.staff.index')
            ->with('success', "‘{$name}’ এর অ্যাকাউন্ট মুছে ফেলা হয়েছে।");
    }
}
