@extends('errors.layout')

@section('title', '500 - Erro interno')

@section('code', '500')

@section('headline', 'Erro interno do servidor')

@section('message')
O médico puxou o cabo errado e o sistema entrou em observação.
Foi um erro inesperado do servidor. Tente novamente em alguns instantes ou avise o time de desenvolvimento.
@endsection

@section('doctor_phrase')
Ops... puxei o cabo errado!
@endsection

@section('monitor_text')
Sistema em emergência
@endsection

@section('accent_color', '#F59E0B')
@section('code_color', 'text-amber-600')
@section('dot_color', 'bg-amber-500')

@section('mouth_svg')
<path d="M448 218 Q468 230 488 218"
      fill="none"
      stroke="#263F38"
      stroke-width="5"
      stroke-linecap="round"/>
@endsection