@extends('errors.layout')

@section('title', '500 - Erro interno do servidor')
@section('code', '500')
@section('accent', '#FFC857')
@section('headline', 'Erro interno do servidor')

@section('message')
Durante a missão para restaurar a conexão do hospital, algo saiu do controle no núcleo do sistema.
O gato enfermeiro puxou o cabo no momento errado, o cachorro enfermeiro entrou em desespero atrás da internet, e o servidor entrou em estado crítico.
@endsection

@section('scene_tag', 'Falha crítica')
@section('cat_phrase', 'Ops... acho que puxei cedo demais!')
@section('dog_phrase', 'Agora caiu tudo?!')
@section('monitor_text', 'Servidor em estado crítico')

@section('caption_title', 'Episódio 500 — pane no servidor')
@section('caption_text', 'O plantão digital entrou em emergência. Aguarde alguns instantes e tente novamente ou avise o time de desenvolvimento.')