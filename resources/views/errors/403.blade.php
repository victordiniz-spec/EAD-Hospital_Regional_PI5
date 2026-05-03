@extends('errors.layout')

@section('title', '403 - Acesso negado')
@section('code', '403')
@section('headline', 'Acesso negado')
@section('message')
O cachorro enfermeiro finalmente alcançou o corredor da internet, mas essa área é restrita.
Mesmo com toda a correria do plantão digital, você não tem permissão para entrar aqui.
@endsection

@section('scene_tag', 'Sala restrita')
@section('cat_phrase', 'Só entra com autorização!')
@section('dog_phrase', 'Mas eu só queria um sinalzinho...')
@section('monitor_text', 'Acesso bloqueado')
@section('monitor_color', '#DC2626')
@section('code_color', '#DC2626')
@section('dot_color', '#DC2626')

@section('caption_title', 'Episódio 403 — a porta trancada')
@section('caption_text', 'O cabo existe, a internet também… mas esta sala é protegida. Seu perfil não tem permissão para acessar esta parte do sistema.')