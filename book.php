<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>书籍详情</title>
    <style type="text/css">
	    #controlPanel {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #f8f8f8; /* 您可以根据需求调整背景颜色 */
        border-top: 1px solid #ddd;
        padding: 10px 0;
        box-sizing: border-box;
        text-align: center; /* 确保按钮水平居中 */
        }
		#controlPanel button {
        margin: 0 2%; /* 在按钮之间添加间距 */
        width: 10%; /* 每个按钮的宽度 */
        padding: 8px 0; /* 按钮的垂直填充 */
        font-size: 30px; /* 字体大小，可根据需要调整 */
}
        。content {
        padding: 20px;
        border: 1px solid #ddd;
        padding-bottom: 60px; /* 调整这个值以确保足够的空间 */
        font-size: 30px;
         }
        。button {
            margin: 5px;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            cursor: pointer;
        }
        。title {
            font-size: 30px;
            text-align: center;
            margin: 10px 0;
        }
    </style>
    <!-- 引入 jQuery 3.1 -->
    <script src="jquery-3.1.1.min.js"></script>
</head>
<body>
    <div id="chapterTitle" class="title">章节标题</div>

    <div id="bookContent" class="content">
        加载中...
    </div>

<!-- 在</body>标签之前添加此部分 -->
<div id="controlPanel">
    <button id="bookShelf">书架</button>
    <button id="prevChapter">上一章</button>
    <button id="nextChapter">下一章</button>
    <button id="chapterList">目录</button>
    <button id="increaseFont">增大</button>
    <button id="decreaseFont">减小</button>
</div>

<script type="text/javascript">
   $(document).ready(function() {
    var bookUrl = getQueryParam('bookUrl');
    var chapterIndex = parseInt(getQueryParam('chapterIndex'), 10);
    var chapters = [];  // 存储章节列表


    // 获取默认字体大小
    var defaultFontSize;

    // 获取服务器保存的字体大小
    getFontSizeFromServer();

    saveBookProgress(bookUrl, chapterIndex);
    getBookContent(bookUrl, chapterIndex);
    getChapterList(bookUrl);

    // 字体大小调整功能
    $('#increaseFont').click(function() {
        defaultFontSize += 2;
        $('#bookContent').css('font-size', defaultFontSize + 'px');
        saveFontSize(defaultFontSize);
    });

    $('#decreaseFont').click(function() {
        defaultFontSize = Math.max(defaultFontSize - 2, 10);
        $('#bookContent').css('font-size', defaultFontSize + 'px');
        saveFontSize(defaultFontSize);
    });

    // 章节切换功能
    $('#prevChapter').click(function() {
        saveBookProgress(bookUrl, chapterIndex);
	window.scrollTo(0，0);
        if (chapterIndex > 0) {
            chapterIndex -= 1;
            updateContentAndTitle();
            updateChapterTitle(chapterIndex);
        }
    });

    $('#nextChapter').click(function() {
        saveBookProgress(bookUrl, chapterIndex);
	window.scrollTo(0, 0);
        if (chapterIndex < chapters.length - 1) {
            chapterIndex += 1;
			            updateContentAndTitle();
            updateChapterTitle(chapterIndex);
        }
    });

    $('#bookShelf').click(function() {
        window.location.href = '/';  // 调整为书架页面的实际 URL
    });
	$('#chapterList').click(function() {
        window.location.href = '/chapterlist.php?bookUrl=' + encodeURIComponent(bookUrl) + '&refresh=' + 0;  // 调整为书架页面的实际 URL
    });

    // 获取 URL 参数的函数
    function getQueryParam(name) {
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        }
        return decodeURIComponent(results[1]) || 0;
    }

    // 更新内容和标题的函数
    function updateContentAndTitle() {
        saveBookProgress(bookUrl, chapterIndex);
        updateChapterTitle(chapterIndex);
        getBookContent(bookUrl, chapterIndex);
    }


    // 保存书籍进度
    function saveBookProgress(bookUrl, index) {
                    $.ajax({
                url: 'api.php?action=saveBookProgress',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ "url": bookUrl, "index": index }),
                success: function(response) {
                    console.log('阅读进度已保存');
                },
                error: function(xhr, status, error) {
                    console.error('保存进度失败: ' + error);
                }
            });
    }

    // 获取书籍内容
    function getBookContent(bookUrl, index) {
                    $.ajax({
                url: 'api.php?action=getBookContent',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ "url": bookUrl, "index": index }),
                success: function(response) {
                    // 解析 JSON 响应
                    var responseObject = JSON.parse(response);
                    if(responseObject.isSuccess) {
                        var content = responseObject.data.replace(/\n/g, '<br>'); // 将换行符替换为 <br>
                        $('#bookContent').html(content);
                    } else {
                        $('#bookContent').html('加载失败: ' + responseObject.errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    $('#bookContent').html('加载失败: ' + error);
                }
            });
    }

    // 获取章节列表
    function getChapterList(bookUrl) {
        $.ajax({
            url: 'api.php?action=getChapterList',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ "url": bookUrl }),
			
            success: function(response) {
				response = JSON.parse(response);
				if (response.isSuccess) {
                    chapters = response.data;
					updateChapterTitle(chapterIndex);
                }
            }
        });
    }

    // 更新章节标题
    function updateChapterTitle(index) {
        if (chapters && chapters.length > index) {
            $('#chapterTitle').text(chapters[index].title);
        }
    }

    // 获取字体大小
        function getFontSizeFromServer() {
            // 获取服务器保存的字体大小
            $.get('api.php?action=getFontSize', function(response) {
                defaultFontSize = parseInt(response, 10) || 30; // 默认字体大小为 30px
                $('#bookContent').css('font-size', defaultFontSize + 'px');
            });
        }

        function saveFontSize(fontSize) {
            // 保存字体大小到服务器
            $.post('api.php?action=saveFontSize', { fontSize: fontSize });
        }
    });

</script>
</body>
</html>
