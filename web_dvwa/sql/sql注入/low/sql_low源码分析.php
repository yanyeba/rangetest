'''
相关函数：
isset ： 如果侦听到指定内容返回true,否则返回false
_REQUEST : 超级全局变量，获取请求与url参数
swich 选择执行代码块
is_object：函数：如果是对象返回true,否则返回false
$GLOBALS["___mysqli_ston"]:连接数据库

三元表达式
表达式 ？ 表达式为真返回（代码1） ： 表达式为假返回（代码2）

mysqli_fetch_assoc：将对象变成关联数组


思路：

对我们输入的内容内有进行任何的过滤，我们知道把前面它的查询语句闭合，我们就能进行任何查询了

'''

<?php
// 如果侦听到Submit变量的请求（不管是get还是post）
if( isset( $_REQUEST[ 'Submit' ] ) ) {
    // Get input 获取输入
    $id = $_REQUEST[ 'id' ];  // 获取请求的参数为id

    switch ($_DVWA['SQLI_DB']) {   //swich 选择执行 相应的代码块  根据用户使用的数据库选择：mysql数据库护或者sqlite数据库
        case MYSQL: 
            // Check database 检测数据库
			//查询语句： 查询first_name, last_name字段从users表 筛选条件user_id = '$id' 
            $query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";  
			//返回查询内容。【连接数据库：$GLOBALS["___mysqli_ston"]】 【查询表内容$query】---后面是连接不上数据库的原因啦
            $result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

            // Get results 获取查询结果
            while( $row = mysqli_fetch_assoc( $result ) ) {  //将返回对象变成一个关联数组
                // Get values 获取值
                $first = $row["first_name"]; // 
                $last  = $row["last_name"];

                // Feedback for end user 最后返回给用户内容 返回输入的id 查询的first与last
                echo "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>"; 
            }
			// 关闭数据库的连接
            mysqli_close($GLOBALS["___mysqli_ston"]);
            break; // 结束
        case SQLITE:
            global $sqlite_db_connection;

            #$sqlite_db_connection = new SQLite3($_DVWA['SQLITE_DB']);
            #$sqlite_db_connection->enableExceptions(true);

            $query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
            #print $query;
            try {
                $results = $sqlite_db_connection->query($query);
            } catch (Exception $e) {
                echo 'Caught exception: ' . $e->getMessage();
                exit();
            }

            if ($results) {
                while ($row = $results->fetchArray()) {
                    // Get values
                    $first = $row["first_name"];
                    $last  = $row["last_name"];

                    // Feedback for end user
                    echo "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                }
            } else {
                echo "Error in fetch ".$sqlite_db->lastErrorMsg();
            }
            break;
    } 
}

?>