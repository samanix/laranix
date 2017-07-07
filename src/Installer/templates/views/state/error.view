@extends('layout.app')

@section('pagetitle', $page_title ?? 'Error')

@section('meta-keywords', 'error')

@section('meta-robots', 'noindex, nofollow')

@section('content')
<div class="ui grid">
    <div class="doubling two column centered row">
        <div class="column">
            <div class="ui visible error message">
                <div class="header">{{ $header ?? "Error" }}</div>
                {!! $message !!}
            </div>
        </div>
    </div>
</div>
@endsection

