@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "nebula";
    $EXTENSION_NAME = stripslashes("Nebula");
    $EXTENSION_VERSION = "2.2-2";
    $EXTENSION_DESCRIPTION = stripslashes("Pterodactyl takes flight");
    $EXTENSION_ICON = "/assets/extensions/nebula/icon.jpg";
    $EXTENSION_WEBSITE = "https://nebula.style";
    $EXTENSION_WEBICON = "bi bi-link-45deg";
?>
@include('blueprint.admin.template')

@section('title')
    {{ $EXTENSION_NAME }}
@endsection

@section('content-header')
    @yield('extension.header')
@endsection

@section('content')
    @yield('extension.config')
    @yield('extension.description')<!--
  =================================
  DEPRECATED IN FAVOR OF NEW EDITOR
  =================================
-->
@endsection
