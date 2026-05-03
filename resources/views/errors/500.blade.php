@extends('errors.layout')

@section('title', '500 - Erro interno do servidor')
@section('code', '500')
@section('headline', 'Erro interno do servidor')
@section('message')
Na tentativa de salvar a internet do hospital, o gato enfermeiro puxou o cabo na hora errada, o cachorro se atrapalhou no corredor
e o sistema entrou em emergência técnica. Foi um erro inesperado do servidor.
@endsection

@section('scene_tag', 'Pane no plantão')
@section('cat_phrase', 'Ops... puxei o cabo errado!')
@section('dog_phrase', 'Agora ninguém tem internet!')
@section('monitor_text', 'Servidor em emergência')
@section('monitor_color', '#F59E0B')
@section('code_color', '#D97706')
@section('dot_color', '#F59E0B')

@section('caption_title', 'Episódio 500 — caos no corredor')
@section('caption_text', 'Quando o plantão digital sai do controle, até o gato enfermeiro e o cachorro enfermeiro entram em pânico. Tente novamente em alguns instantes.')