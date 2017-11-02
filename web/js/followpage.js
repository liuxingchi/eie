function QueryString(){
	var currentPage = $("#currentPage").val();
	if(currentPage==""){currentPage = 1;}
	//var sValue=currentPage.search.match(new RegExp("[\?\&]"+item+"=([^\&]*)(\&?)","i"));
	return currentPage
}

function loadpage(oClick){
	var start = oClick.title;
	var startnum = (start-1)*30+"";
	$("#currentPage").val(oClick.title);
	
	var json = {
		"start": startnum,
		"num": "30"
		};

reloadList(json,1);

}
function showPage(count,current){
				var count = count;
				var current = current;
				var perpage = 30; //每页个数
				if(current==0){
					var currentpage = parseInt(1);
					}else{
						var currentpage = QueryString();
						currentpage = parseInt(currentpage);//当前页
					}
				var pagecount = Math.ceil(count/perpage); //总页数
				var pagestr = ""; //分页显示内容
				var breakpage = 9;
				var currentposition = 4;
				var breakspace = 2;
				var maxspace = 4;
				var prevnum = currentpage-currentposition;
				var nextnum = currentpage+currentposition;
				if(prevnum<1) prevnum = 1;
				if(nextnum>pagecount) nextnum = pagecount;
pagestr += (currentpage==1)?'<span class="prev"></span>':'<span class="prev"><a href="?page='+(currentpage-1)+'"></a></span>';
				if(prevnum-breakspace>maxspace){
					for(i=1;i<=breakspace;i++)
					pagestr += '<li><a href="#" onclick="loadpage(this)" title='+i+'>'+i+'</a></li>';
					pagestr += '<li class="break"><a href="#">...</a></li>';
					for(i=pagecount-breakpage+1;i<prevnum;i++)
						pagestr += '<li><a href="#" onclick="loadpage(this)" title='+i+'>'+i+'</a></li>';
				}else{
					for(i=1;i<prevnum;i++)
						pagestr += '<li><a href="#" onclick="loadpage(this)" title='+i+'>'+i+'</a></li>';
				}
				for(i=prevnum;i<=nextnum;i++){
					pagestr += (currentpage==i)?'<li class="active"><a href="#">'+i+'</a></li>':'<li><a href="#" onclick="loadpage(this)" title='+i+'>'+i+'</a></li>';
				}
				if(pagecount-breakspace-nextnum+1>maxspace){
					for(i=nextnum+1;i<=breakpage;i++)
						pagestr += '<li><a href="#" onclick="loadpage(this)" title='+i+'>'+i+'</a></li>';
					pagestr += '<li class="break"><a>...</a></li>';
					for(i=pagecount-breakspace+1;i<=pagecount;i++)
						pagestr += '<li><a href="#" onclick="loadpage(this)" title='+i+'>'+i+'</a></li>';
				}else{
					for(i=nextnum+1;i<=pagecount;i++)
						pagestr += '<li><a href="#" onclick="loadpage(this)" title='+i+'>'+i+'</a></li>';
				}
				pagestr += (currentpage==pagecount)?'<span class="next"></span>':'<span class="next"><a href="?page='+(currentpage+1)+'"></a></span>';
				document.getElementById("pagebar").innerHTML = pagestr;
	
	}
function page(current){
	
	var current = current;
	//获得总数据量
	//alert(KEYWORD);
	var json = {};
            ajaxPost(
              'app_dev.php/api/follownews_count',
              json,
              function(data, textStatus){
				
           		var num = data;//总条数
				if(num==undefined){var count = 0;}else{var count = num;}
				$("#index_list_num").html("共"+count+"条");
				//alert(count);
				if(current == 0)
					{
					this.showPage(count,0)
					}
				else{
					this.showPage(count,1);
					}
				},
              function(XMLHttpRequest, textStatus, errorThrown){  
               switch(XMLHttpRequest.status)
				{
					case 400: //没数据
						break;
					default:
						//alert('加载页码出现错误，请重试');
				}
			}
          );
	
	
	}
