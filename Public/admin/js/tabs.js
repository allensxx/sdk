window.onload = function(){

        tabPage({
            pageMain : 'pageMain',
            pageNav : 'pageNav',
            pagePrev: 'prev',
            pageNext: 'next',
            curNum: 10,      //每页显示的条数
            activeClass: 'active',   //高光显示的class
            ini: 0    //初始化显示的页面
        });

        function tabPage(tabPage){
            var pageMain = document.getElementById(tabPage.pageMain);   //获取内容列表
            var pageNav = document.getElementById(tabPage.pageNav);     //获取分页
            var pagePrev = document.getElementById(tabPage.pagePrev);   //上一页
            var pageNext = document.getElementById(tabPage.pageNext);   //下一页

            var curNum = tabPage.curNum;                                //每页显示数
            var len = Math.ceil(pageMain.children.length / curNum);console.log(len) ;     //计算总页数
            var pageList = '';                                          //生成页码
            var iNum = 0;                                               //当前的索引值index

            for(var i = 0; i < len; i++){
                pageList+='<a href="javascript:;">'+ ( i + 1)+'</a>';
            }
            pageNav.innerHTML = pageList;

            pageNav.children[0].className = tabPage.activeClass;        //头一页加高亮显示

            for(var i = 0; i < pageNav.children.length; i++){
                pageNav.children[i].index = i;
                pageNav.children[i].onclick = function(){
                    for(var t = 0; t < pageNav.children.length; t++){
                        pageNav.children[t].className = '';
                    }
                    this.className = tabPage.activeClass;
                    iNum = this.index;
                    ini(iNum);
                };
            }


            //下一页
            pageNext.onclick = function(){
                if(iNum == len - 1){
                    alert('已经是最后一页');
                    return false;
                }else{
                    for(var t = 0; t < pageNav.children.length; t++){
                        pageNav.children[t].className = '';
                    }
                    iNum++;
                    pageNav.children[iNum].className = tabPage.activeClass;
                    ini(iNum);
                }
            };

            //上一页
            pagePrev.onclick = function(){
                if(iNum == 0){
                    alert('当前是第一页');
                    return false;
                }else{
                    for(var t = 0; t < pageNav.children.length; t++){
                        pageNav.children[t].className = '';
                    }
                    iNum--;
                    pageNav.children[iNum].className = tabPage.activeClass;
                    ini(iNum);
                }
            };

            function ini(iNum){
                for(var i = 0; i < pageMain.children.length; i++){
                    pageMain.children[i].style.display = 'none';
                }

                for(var i = 0; i < curNum; i++){
                    if(pageMain.children[(iNum * curNum + i)] == undefined){ continue; }
                    pageMain.children[(iNum * curNum + i)].style.display = 'table-row';
                }
            }

            ini(iNum);

        }
    };
