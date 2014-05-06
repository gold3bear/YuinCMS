@extends('default.layouts.master')

@section('title')
{{$article->title}} | 
@parent
@stop

@section('head')
<meta name="description" content="{{{$article->description}}}" />
<meta name="keywords" content="{{implode(',', $article->tags()->lists('name'))}}" />
@stop

@section('main')

<div class="w-main">

    <div class="m-post" id="post_{{$article->id}}" data-url="{{$article->url()}}">

        <!-- 评论数 -->
        <a href="{{$article->url()}}#js-weibo-comments" class="comment-count" id="js-goto-comment-box" title="《{{$article->title}}》上的评论" no-scroll>
            {{$article->comments}}
        </a>



        <!-- 标题 -->
        <h1 class="post-title">
            <a href="{{$article->url()}}">{{$article->title}}</a>
        </h1>

        <!-- 文章信息 -->
        <div class="meta">
            <!-- 作者链接 -->
            <a href='{{url("author/{$article->user_id}")}}' target="_blank" class="author">
                @if ($article->author)
                {{$article->author}}
                @else
                {{$article->user->username}}
                @endif
            </a>


            <!-- 多少天前 -->
            <span class="date">
                {{date('Y-m-d,H:i',$article->published)}}
            </span>


            <!-- 分类 -->
            @if(!$article->categories->isEmpty())
            <a title="查看 {{$article->categories->first()->name}} 分类的所有文章" class="category {{$article->categories->first()->slug}}" href="{{--$article->categories->url()--}}">
                发布于 {{$article->categories->first()->name}}
            </a>
            @endif

            <!-- 浏览数 -->
            <span class="views">热度：{{$article->views}}</span>

            @if(Auth::check())
            <!--有编辑他人权限-->
            / <a href="{{url('edit/'.$article->id)}}">编辑此文</a>
            @endif
        </div>




        @if($imgurl = $article->banner_url( array('mw' => 640, 'wm' => 1, 'wmp' => 3) ))
        <div class="banner">
            <a href="" title="{{{$article->title}}}">
                <img src="{{$imgurl}}" alt="{{{$article->title}}}">
            </a>
        </div>
        @endif



        <!-- 文章导航 -->
        <div class="post-inner-nav-top">
        </div>



        <div class="typo">
            {{$article->getContent(array('img_args' => array('mw' => 640, 'wm' => 0),'page'=>Input::get('page')))}}
        </div>

        @if ($pageinfo = $article->pageinfo(1))
        <div class="m-pages">
            @include('default.pager')
        </div>
        @endif
        <script type="text/javascript">
            // 延迟加载
            jQuery(document).ready(function($) {
                $(".typo img").lazyload({
                    effect: "fadeIn",
                    placeholder: "resource/_img/empty.gif"
                });
            });
        </script>
        <!-- 文章结束线 -->
        <div class="end-content-line"></div>



        <!-- 版权 -->
        <!-- 文章来源 [站名带链接]，原作者 [作者名] -->
        <div class="copyright">
            <span class="icon-copyright"></span>
            文章来源
            @if($article->source_name)

            @if($article->source_name || $article->source_url())
            <a href="{{$article->source_url()}}" title="{{$article->source_name}}" target="_blank">
                {{$article->source_name}}
            </a>
            @else
            @endif

            @if($article->author)
            ，原作者 {{$article->author}}
            @endif

            @else
            @endif
            转载请注明原文链接。
        </div>




        <!-- 标签 -->
        <div class="tags">
            <label>标签:</label>

            @foreach ($article->tags as $tag)
            <a rel="tag" href="{{$tag->url()}}" title="查看所有关于 {{$tag->name}} 的文章">
                {{$tag->name}}
            </a>
            @endforeach

        </div>



        <!-- 上一篇文章和下一篇文章 -->
        <div class="post-nav">
            @if($next_post = $article->next())
            <a rel="next" class="prev" href="{{$next_post->url()}}">{{$next_post->title}}</a>
            <div class="inner">
                <img src="{{$next_post->banner_url( array('w' => 184, 'h' => 90) )}}" alt="{{$next_post->title}}" class="thumb">
                <div class="info">
                    <div class="date">{{date('Y-m-d,H:i',$next_post->published)}}</div>
                    <h4>{{$next_post->title}}</h4>
                </div>
            </div>
            @endif

            @if($previous_post = $article->previous())
            <a rel="previous" class="next" href="{{$previous_post->url()}}">{{$previous_post->title}}</a>
            <div class="inner">
                <img src="{{$previous_post->banner_url( array('w' => 184, 'h' => 90) )}}" alt="{{$previous_post->title}}" class="thumb">
                <div class="info">
                    <div class="date">{{date('Y-m-d,H:i',$previous_post->published)}}</div>
                    <h4>{{$previous_post->title}}</h4>
                </div>
            </div>
            @endif
        </div>





    </div>
    <!-- 文章 END -->

</div>



@stop



























