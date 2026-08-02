@extends('layouts.minimal')
@section('title', 'Không có quyền truy cập')
@section('content')
<main class="error-page"><x-ui.icon name="shield" size="48" /><h1>403</h1><h2>Bạn không có quyền thực hiện thao tác này</h2><p>Vui lòng liên hệ quản trị viên nếu bạn cho rằng đây là nhầm lẫn.</p><a class="button button-primary" href="{{ url()->previous() }}">Quay lại</a></main>
@endsection
