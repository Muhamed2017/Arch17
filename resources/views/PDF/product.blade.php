<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Arch17|APIs</title>
        <style>
@import url('https://fonts.googleapis.com/css2?family=ZCOOL+XiaoWei&display=swap');
.chinese{
    font-family: 'ZCOOL XiaoWei', serif !important;
    font-weight: normal !important;
}
*{
    box-sizing: border-box !important;
}
.container{
    width: 100%;
    /* background: red */
}
.container table{
    width: 100%;
}
img{
    display: block;
    width:100%;
}
.left{
    width:49%;
    display: inline-block;

}
.content{
    display: block;
    width: 100%;
}
.right{
    display:inline-block;
    vertical-align:sub;
    width:48%;
    padding-left:17px;
    text-align: left;
    max-width: 50vw;

}
.right p{
    font-size: 1.1rem;
    color:rgba(0, 0, 0, 0.644);
}
.right h2{
    text-decoration: underline;
    font-size: 1.3rem;
}
.main-img{
    max-width: 100%;
}
.text-sec{
    margin-bottom:80px;
}
.thumbs div{
display: inline-block;
margin:2px;
}
.thumb{
width:100px;
height: 100px;
background-repeat: no-repeat;
background-position: center;
background-size: contain;
}


.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:12px;
  overflow:hidden;padding:18px 8px;word-break:normal;}
.tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:12px;
  font-weight:normal;overflow:hidden;padding:18px 8px;word-break:normal;}
.tg .tg-0lax{text-align:left;vertical-align:middle;}
.data{
    font-weight: bold;
}
        </style>
    </head>
    <body>
        <div style="text-align: right; color:rgba(65, 65, 65, 0.582)">
            <p>Source <span class="chinese">来源</span> www.arch17.com</p>
        </div>
        <hr>
        <div class="container">
        <table class="tg">
<thead>
  <tr>
    <td class="tg-0lax">
        <div>
            Name <span class="chinese">来源</span>
        </div>
    </td>
    <td class="tg-0lax data">
{{-- LUNA SOFA (2 Seats) --}}
{{$data['name']}}

    </td>
    <td class="tg-0lax" rowspan="2">
       Type <span class="chinese">类型</span>
    </td>
    <td class="tg-0lax data" rowspan="2">
        {{-- Sofa --}}
        {{$data['kind']}}
    </td>
    <td class="tg-0lax" rowspan="2">
       Brand <span class="chinese">制作商</span>
    </td>
    <td class="tg-0lax data" rowspan="2">
        {{$data['brand']}}
    </td>
    <td class="tg-0lax" rowspan="2">
       Source Link <span class="chinese">源码链接</span>
    </td>
    <td class="tg-0lax data" rowspan="2">
        {{$data['link']}}
    </td>
  </tr>
  <tr>
    <td class="tg-0lax">
           <div>
            Model <span class="chinese">来源</span>
        </div>
    </td>
    <td class="tg-0lax data">
        10-10
    </td>
  </tr>
</thead>
</table>
    </div>
        <div class="content">
            <div class="left">
                <div class="main-img">
                    <img src={{$data['image']}} alt="">
                </div>
                <div class="thumbs">
                    @foreach ($data['covers'] as $cover)
                        {{-- <img src={{$cover['src']}} alt=""> --}}
                        <div class="thumb" style="background-image: url({{$cover['src']}})">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="right">
               <div class="text-sec">
                    <h2>Descriotion</h2>
                    <p>The elegant Luna came from the Dada, Alex continued to use the elements of the high heels on the
                        sofa. While supporting the entire seat and ensuring the comfort of the Luna, the close combination of
                        back and armrest cushion makes an integral line simple and elegant. The angle between the seat and
                        backrest totally based on human engineering to meet users’ need for comfort. Luna is an excellent
                        choice for both residential and public space because of the elegant form. Not only noble, but also
                        elegant. Visit Products Source link to see more details
                    </p>
               </div>
               <div class="text-sec">
                   <h2>Dimenstion</h2>
            </div>
        </div>
        <hr>
    </body>
</html>
