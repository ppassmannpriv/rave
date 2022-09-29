<!doctype html>
<title>SCHLEUSE EINS | Rave</title>
<link href="{{ asset('css/fonts.css') }}" rel="stylesheet" />
<style>
    html, body, .article-wrap { height: 100%; }
    body { text-align: center; }
    h1 { font-size: 3.5rem; font-family: '911_porscha_semi-italicSIt'; }
    body { font: 1.5rem Helvetica, sans-serif; color: #333; background: black; color: white; margin: 0; }
    .article-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    article {
        width: 29.5em;
        height: 10em;
        text-align: left;
    }
    a { color: #dc8100; text-decoration: none; }
    a:hover { color: #333; text-decoration: none; }
    .gradient1 {
        color: #A02D0A;
        background: linear-gradient(90deg, rgba(255,0,159,1) 0%, rgba(255,145,0,1) 50%, rgba(162,0,255,1) 100%);
        /* background: linear-gradient(90deg, rgba(255,0,159,1) 0%, rgba(162,0,255,1) 25%, rgba(0,211,255,1) 50%, rgba(255,145,0,1) 75%, rgba(255,0,0,1) 100%); */
    }

    body .gradient {
        padding-right: .125em;
        background-repeat: no-repeat;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        -webkit-transition: .5s 0s cubic-bezier(.25,.46,.45,.94);
        transition: .5s 0s cubic-bezier(.25,.46,.45,.94);
    }

    #logo {
        width: auto;
    }

    .bar {
        width: 1000%;
        height: 5px;
        background: linear-gradient(90deg, rgba(255,0,159,1) 0%, rgba(255,145,0,1) 50%, rgba(162,0,255,1) 100%);
        animation-name: bar;
        animation-duration: 6s;
        animation-direction: alternate;
        animation-iteration-count: infinite;
    }

    .bar-wrap {
        width: 100%;
        overflow: hidden;
    }

    @keyframes bar {
        from {margin-left: 0;}
        to {margin-left: -900%;}
    }

    /* iPhone XR */
    /*@media only screen
    and (device-width: 414px)
    and (device-height: 896px)
    and (-webkit-device-pixel-ratio : 2) {*/
    @media screen and (max-width: 63rem) {
        h1 { font-size: 2em; }
        body { font-size: 2em; }
        .article-wrap { align-items: start; margin-top: 1em; }
        article { padding: .5em; width: 25.2em; padding: 0; }
    }

    @media screen and (max-width: 480px) {
        h1 { font-size: 2em; }
        body { font-size: .75em; }
        article { padding: .5em;}
    }
</style>
<div class="bar-wrap"><div class="bar"></div></div>
<div class="article-wrap">
    <article>
        <h1 id="logo" class="gradient1 gradient">SCHLEUSE EINS</h1>
        <div>
            <p>Sorry for the inconvenience! We&rsquo;ll be online shortly!</p>
            <p>&mdash; never stop raving</p>
        </div>
    </article>
</div>
