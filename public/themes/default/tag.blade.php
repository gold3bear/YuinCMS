@extends('default.layouts.master')

@section('title')
「{{$tag}}」标签搜索结果 | 
@parent
@stop

@section('head')
@parent
@stop

@section('main')
<div class="w-main">

    <!-- 文章列表 -->
    <div class="m-block m-post-list">
        <h2><span class="title-icon-arrow"></span>{{$tag}} </h2>

        <!-- 頁中內容 START -->
        <ul class="block-content">
            @include('default.posts')
        </ul>
        <!-- 文章列表结束 -->
        <!-- 翻页器 -->
        {{$articles->links()}}
    </div>
</div>
@stop