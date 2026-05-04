@extends('errors.layout')

@section('title', '403 - Acesso negado')
@section('code', '403')
@section('accent', '#FF5F73')
@section('headline', 'Acesso negado')

@section('message')
O sistema detectou sua presença no corredor, mas esta área é protegida.
Mesmo com toda a pressa do gato enfermeiro e a insistência do cachorro enfermeiro atrás de internet, esta seção continua bloqueada para o seu perfil.
@endsection

@section('scene_tag', 'Acesso restrito')
@section('cat_phrase', 'Área protegida! Não passa!')
@section('dog_phrase', 'Mas eu só queria um sinalzinho...')
@section('monitor_text', 'Permissão insuficiente')

@section('caption_title', 'Episódio 403 — porta bloqueada')
@section('caption_text', 'A conexão existe, a rota também, mas esta área exige autorização. Seu perfil não possui permissão para entrar aqui.')