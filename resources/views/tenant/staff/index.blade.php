@extends('master.tenant')

@section('title', 'স্টাফ ও ক্যাশিয়ার ব্যবস্থাপনা')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="page-title mb-1 font-weight-bold">
            <i class="fas fa-users-cog text-primary mr-2"></i> স্টাফ ও ক্যাশিয়ার ব্যবস্থাপনা
        </h4>
        <p class="text-muted mb-0 font-14">দোকানের ম্যানেজার, ক্যাশিয়ার নিয়োগ ও রোল পারমিশন নিয়ন্ত্রণ</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#createStaffModal">
            <i class="fas fa-user-plus mr-2"></i> নতুন স্টাফ যোগ করুন
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="font-weight-bold text-dark mb-0">সকল স্টাফ ও পরিচালকদের তালিকা</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>নাম</th>
                        <th>মোবাইল নম্বর (লগইন আইডি)</th>
                        <th>ইমেইল</th>
                        <th class="text-center">রোল / পদবি</th>
                        <th class="text-center">স্ট্যাটাস</th>
                        <th>যুক্ত হওয়ার তারিখ</th>
                        <th class="text-center" style="width: 140px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffMembers as $idx => $staff)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $staff->name }}</div>
                        </td>
                        <td>
                            <span class="font-weight-bold text-primary">{{ $staff->phone }}</span>
                        </td>
                        <td>{{ $staff->email ?? 'ইমেইল নেই' }}</td>
                        <td class="text-center">
                            @if($staff->role === 'owner')
                                <span class="badge badge-owner px-3 py-1 font-weight-bold"><i class="fas fa-crown mr-1"></i> Owner</span>
                            @elseif($staff->role === 'manager')
                                <span class="badge badge-manager px-3 py-1 font-weight-bold"><i class="fas fa-user-tie mr-1"></i> Manager</span>
                            @else
                                <span class="badge badge-cashier px-3 py-1 font-weight-bold"><i class="fas fa-cash-register mr-1"></i> Cashier</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($staff->is_active)
                                <span class="badge badge-success">সক্রিয়</span>
                            @else
                                <span class="badge badge-danger">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td>{{ $staff->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editStaffModal-{{ $staff->id }}" title="এডিট ও পাসওয়ার্ড রিসেট">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($staff->role !== 'owner' && Auth::guard('tenant_web')->id() !== $staff->id)
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $staff->id }}, '{{ addslashes($staff->name) }}')" title="মুছে ফেলুন">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @endif
                            </div>

                            @if($staff->role !== 'owner')
                            <form id="delete-staff-form-{{ $staff->id }}" action="{{ route('tenant.staff.destroy', $staff->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                        </td>
                    </tr>

                    <!-- Edit Staff Modal -->
                    <div class="modal fade" id="editStaffModal-{{ $staff->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <form action="{{ route('tenant.staff.update', $staff->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title font-weight-bold">
                                            <i class="fas fa-user-edit mr-2"></i> স্টাফ আপডেট: {{ $staff->name }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group px-0 mb-3">
                                            <label class="font-weight-bold">পূর্ণ নাম <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $staff->name }}" required>
                                        </div>
                                        <div class="form-group px-0 mb-3">
                                            <label class="font-weight-bold">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                            <input type="text" name="phone" class="form-control" value="{{ $staff->phone }}" required>
                                        </div>
                                        <div class="form-group px-0 mb-3">
                                            <label class="font-weight-bold">ইমেইল</label>
                                            <input type="email" name="email" class="form-control" value="{{ $staff->email }}">
                                        </div>
                                        <div class="form-group px-0 mb-3">
                                            <label class="font-weight-bold">রোল / পদবি</label>
                                            <select name="role" class="form-control">
                                                @if($staff->role === 'owner')
                                                    <option value="owner" selected>দোকানের মালিক (Owner)</option>
                                                @endif
                                                <option value="manager" {{ $staff->role === 'manager' ? 'selected' : '' }}>ম্যানেজার (Manager - পূর্ণ ইনভেন্টরি)</option>
                                                <option value="cashier" {{ $staff->role === 'cashier' ? 'selected' : '' }}>ক্যাশিয়ার (Cashier - শুধু POS ও সেল)</option>
                                            </select>
                                        </div>
                                        <div class="form-group px-0 mb-3">
                                            <label class="font-weight-bold">নতুন পাসওয়ার্ড (পরিবর্তন না করতে চাইলে ফাঁকা রাখুন)</label>
                                            <input type="password" name="password" class="form-control" placeholder="কমপক্ষে ৬ অক্ষর">
                                        </div>
                                        <div class="custom-control custom-checkbox my-2">
                                            <input type="checkbox" class="custom-control-input" id="is_active_{{ $staff->id }}" name="is_active" value="1" {{ $staff->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="is_active_{{ $staff->id }}">অ্যাকাউন্ট সক্রিয় রাখুন</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">বাতিল</button>
                                        <button type="submit" class="btn btn-primary font-weight-bold">আপডেট সংরক্ষণ করুন</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Staff Modal -->
<div class="modal fade" id="createStaffModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('tenant.staff.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-plus mr-2"></i> নতুন স্টাফ বা ক্যাশিয়ার নিয়োগ
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">স্টাফের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="যেমন: তানভীর আহমেদ" required value="{{ old('name') }}">
                    </div>
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">মোবাইল নম্বর (লগইন ইউজারনেম) <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="০১XXXXXXXXX" required value="{{ old('phone') }}">
                    </div>
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">ইমেইল (ঐচ্ছিক)</label>
                        <input type="email" name="email" class="form-control" placeholder="staff@example.com" value="{{ old('email') }}">
                    </div>
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">পদবি / রোল <span class="text-danger">*</span></label>
                        <select name="role" class="form-control" required>
                            <option value="cashier">ক্যাশিয়ার (Cashier - কাউন্টার বিক্রয় ও মেমো)</option>
                            <option value="manager">ম্যানেজার (Manager - স্টক ও পারচেজ পরিচালনা)</option>
                        </select>
                    </div>
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">লগইন পাসওয়ার্ড <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="কমপক্ষে ৬ অক্ষর" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-check mr-1"></i> স্টাফ সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(id, name) {
        if (confirm(`আপনি কি নিশ্চিত যে ‘${name}’ এর স্টাফ অ্যাকাউন্ট মুছে ফেলতে চান?`)) {
            document.getElementById(`delete-staff-form-${id}`).submit();
        }
    }
</script>
@endsection
