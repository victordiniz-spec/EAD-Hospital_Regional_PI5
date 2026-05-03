@extends('errors.layout')

@section('title', '404 - Página não encontrada')

@section('code', '404')

@section('headline', 'Página não encontrada')

@section('message')
O médico procurou em todos os prontuários, conferiu o monitor, olhou embaixo da maca... mas essa página realmente não foi encontrada.
Talvez o endereço esteja errado ou essa área tenha sido removida.
@endsection

@section('doctor_phrase')
Doutor, essa página sumiu!
@endsection

@section('monitor_text')
Página não encontrada
@endsection

@section('accent_color', '#00A63E')
@section('code_color', 'text-[#004D3A]')
@section('dot_color', 'bg-green-600')

@section('mouth_svg')
<path d="M448 214 Q468 204 488 214"
      fill="none"
      stroke="#263F38"
      stroke-width="5"
      stroke-linecap="round"/>
@endsection