@extends('layouts.admin')

@section('title', '申請一覧 - 管理者')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-common.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-corrections.css') }}">
@endsection

@section('content')
<div class="admin-container">
    <h2>申請一覧</h2>

    <!-- タブ切り替え -->
    <div class="admin-tab-container">
        <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}"
            class="admin-tab-button {{ $tab === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}"
            class="admin-tab-button {{ $tab === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <!-- 承認待ちタブの内容 -->
    @if($tab === 'pending')
    <div class="admin-tab-content active">
        @if($pendingRequests->isEmpty())
        <p class="admin-no-data">承認待ちの申請はありません。</p>
        @else
        <table class="admin-correction-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingRequests as $request)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('stamp_correction_request.show', $request->id) }}" class="btn-detail">
                            詳細
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="no-data">承認待ちの申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @endif
    </div>
    @endif

    <!-- 承認済みタブの内容 -->
    @if($tab === 'approved')
    <div class="admin-tab-content active">
        @if($approvedRequests->isEmpty())
        <p class="admin-no-data">承認済みの申請はありません。</p>
        @else
        <table class="admin-correction-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedRequests as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('stamp_correction_request.show', $request->id) }}" class="btn-detail">
                            詳細
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="no-data">承認済みの申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @endif
    </div>
    @endif
</div>
@endsection