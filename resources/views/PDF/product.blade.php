<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Arch17|APIs</title>
        <style>

/* @import url('https://fonts.googleapis.com/css2?family=ZCOOL+XiaoWei&display=swap'); */
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@500&display=swap');

.chinese{
    /* font-family: 'ZCOOL XiaoWei', serif !important; */
    font-family: 'Noto Sans TC', sans-serif !important;
    font-weight: normal !important;
}
div{
    box-sizing: border-box !important;
}
body{
}
.wrapper{
    position: relative;

}
.container{
    width: 100%;
}
.container table{
    width: 100%;
}
img{
    display: block;
    width:100%;
}
.left{
    width:48%;
    display: inline-block;
    vertical-align: top;
}
.content{
    display: block;
    width: 100%;
}
.right{
    display:inline-block;
    width:50%;
    padding-left:15px;
    text-align: left;
    vertical-align: top;


}
.right p{
    font-size: .9rem;
    color:rgba(0, 0, 0, 0.644);
}
.right h2{
    text-decoration: underline;
    font-size: 1.3rem;
}
.main-img{
    width:100%;
    height:350px;
    background-repeat: no-repeat;
    background-size: contain;
    background-position: center;
}
.thumbs div{
display: inline-block;
margin-top: 10px;
}
.thumb{
width:70px;
height: 70px;
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
        <div class="wrapper">
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
                <div class="main-img" style="background-image: url({{$data['image']}})">
                </div>
                <div class="thumbs">
                    @foreach ($data['covers'] as $cover)
                        <div class="thumb" style="background-image: url({{$cover['src']}})">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="right">
               <div class="text-sec">
                    <h2>Description <span class="chinese">描述</span></h2>
                    <p>The elegant Luna came from the Dada, Alex continued to use the elements of the high heels on the
                        sofa. While supporting the entire seat and ensuring the comfort of the Luna, the close combination of
                        back and armrest cushion makes an integral line simple and elegant. The angle between the seat and
                        backrest totally based on human engineering to meet users’ need for comfort. Luna is an excellent
                        choice for both residential and public space because of the elegant form. Not only noble, but also
                        elegant. Visit Products Source link to see more details
                    </p>
               </div>
               <div class="text-sec">
                   <h2>Dimensions <span class="chinese">方面</span></h2>
            </div>
        </div>
        <hr style="position:absolute; bottom:10px; left:0; right:0; width:100%">
        <div style="position:absolute; bottom:-22px; left:0; right:0; width:100%">
            <p class="bold" style="color: rgba(0, 0, 0, 0.705);">Go More Info: <span style="font-weight: 300">Email:</span> <span class="chinese">邮箱: </span> Sales@arch17.co   <span class="chinese">电话 :</span> <span class="bold">0086 185 7599 9560 </span></p>
        </div>
        </div>
    </body>
</html>
