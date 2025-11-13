@extends('layouts.app')

@section('title', '申請一覧 - COACHTECH')

@section('content')
<div class="attendance-correction-index-container">
    <h2>申請一覧</h2>

    <!-- タブ -->
    <div class="tab-container">
        <button class="tab-button active" data-tab="pending">承認待ち</button>
        <button class="tab-button" data-tab="approved">承認済み</button>
    </div>

    <!-- 承認待ちテーブル -->
    <div id="pending-tab" class="tab-content active">
        @if($pendingRequests->isEmpty())
            <p class="no-data">承認待ちの申請はありません</p>
        @else
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
                    @foreach($pendingRequests as $request)
                        <tr>
                            <td>承認待ち</td>
                            <td>{{ $request->attendance->user->name }}</td>
                            <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                            <td>{{ Str::limit($request->note, 20) }}</td>
                            <td>{{ $request->created_at->format('Y/m/d') }}</td>
                            <td><a href="{{ route('attendance.detail', $request->attendance_id) }}" class="btn-detail">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- 承認済みテーブル -->
    <div id="approved-tab" class="tab-content">
        @if($approvedRequests->isEmpty())
            <p class="no-data">承認済みの申請はありません</p>
        @else
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
                    @foreach($approvedRequests as $request)
                        <tr>
                            <td>承認済み</td>
                            <td>{{ $request->attendance->user->name }}</td>
                            <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                            <td>{{ Str::limit($request->note, 20) }}</td>
                            <td>{{ $request->created_at->format('Y/m/d') }}</td>
                            <td><a href="{{ route('attendance.detail', $request->attendance_id) }}" class="btn-detail">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<script>
    // タブ切り替え機能
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');

                // すべてのタブボタンとコンテンツから active クラスを削除
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // クリックされたタブボタンとコンテンツに active クラスを追加
                this.classList.add('active');
                document.getElementById(targetTab + '-tab').classList.add('active');
            });
        });
    });
</script>
@endsection
