@extends('layouts.auth')

@section('title', 'メール認証')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
<link rel="stylesheet" href="{{ asset('css/verification.css') }}">
@endpush

@section('content')
<div class="auth__content">
    <div class="verification__content">
        @if (session('status'))
        <div class="form__success">
            {{ session('status') }}
        </div>
        @endif

        @if (session('message'))
        <div class="form__info">
            {{ session('message') }}
        </div>
        @endif

        <div class="verification__message">
            <p class="message-line1">登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p class="message-line2">メール認証を完了してください。</p>
        </div>

        <!-- Mailtrapでの認証メール確認と再送 -->
        <div class="verification__actions">
            <div class="verification__button">
                <a href="https://mailtrap.io/inboxes" target="_blank" class="form__button-gray">
                    認証はこちらから
                </a>
            </div>

            <div class="verification__resend">
                <form method="POST" action="{{ route('verification.send') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="resend__link">認証メールを再送する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection