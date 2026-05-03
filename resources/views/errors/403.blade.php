@extends('errors.layout')

@section('title', '403 - Acesso negado')

@section('code', '403')

@section('headline', 'Acesso negado')

@section('message')
O médico até tentou abrir essa sala, mas a porta está trancada.
Você não tem permissão para acessar esta área do sistema ou seu acesso ainda não foi liberado.
@endsection

@section('doctor_phrase')
Essa sala é restrita!
@endsection

@section('monitor_text')
Acesso bloqueado
@endsection

@section('accent_color', '#DC2626')
@section('code_color', 'text-red-600')
@section('dot_color', 'bg-red-600')

@section('mouth_svg')
<path d="M450 216 Q468 226 486 216"
      fill="none"
      stroke="#263F38"
      stroke-width="5"
      stroke-linecap="round"/>
@endsection