@extends('layouts.admin')

@section('title', 'スタッフ一覧 - 管理者')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-staff.css') }}">
@endsection

@section('content')
<div class="admin-container">
    <h2>スタッフ一覧</h2>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="{{ route('admin.attendance.staff', ['user_id' => $user->id]) }}" class="btn-detail">
                        詳細
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="no-data">スタッフが登録されていません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection