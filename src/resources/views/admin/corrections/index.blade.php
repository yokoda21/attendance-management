@extends('layouts.admin')

@section('content')
<div class="correction-list-container">
    <h2 class="correction-list-title">申請一覧</h2>

    <!-- タブ切り替え -->
    <div class="correction-tabs">
        <a href="{{ route('admin.corrections.index', ['tab' => 'pending']) }}" 
           class="correction-tab {{ $tab === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('admin.corrections.index', ['tab' => 'approved']) }}" 
           class="correction-tab {{ $tab === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <!-- 承認待ちタブの内容 -->
    @if($tab === 'pending')
        <div class="correction-table-wrapper">
            <table class="correction-table">
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
                                <a href="{{ route('admin.corrections.show', $request->id) }}" class="detail-link">
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
        </div>
    @endif

    <!-- 承認済みタブの内容 -->
    @if($tab === 'approved')
        <div class="correction-table-wrapper">
            <table class="correction-table">
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
                                <a href="{{ route('admin.corrections.show', $request->id) }}" class="detail-link">
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
        </div>
    @endif
</div>
@endsection
