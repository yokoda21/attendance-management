@extends('layouts.admin')

@section('content')
<div class="correction-requests-container">
    <h1 class="page-title">申請一覧</h1>
    
    <div class="tabs">
        <a href="{{ route('admin.correction-requests.index', ['tab' => 'pending']) }}" 
           class="tab {{ $tab === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('admin.correction-requests.index', ['tab' => 'approved']) }}" 
           class="tab {{ $tab === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>
    
    @if($tab === 'pending')
        <!-- 承認待ちタブ -->
        <div class="correction-requests-table">
            <table>
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
                            <td>{{ $request->reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('admin.correction-requests.show', $request->id) }}" class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-data">承認待ちの申請はありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <!-- 承認済みタブ -->
        <div class="correction-requests-table">
            <table>
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
                            <td>{{ $request->reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('admin.correction-requests.show', $request->id) }}" class="detail-link">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-data">承認済みの申請はありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
