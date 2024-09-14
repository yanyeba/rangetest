/*
$_SESSION: $_SESSION 是一个超全局数组，用于在多个页面请求之间存储和管理用户会话数据。会话（session）是用户与网站之间的交互过程中，服务器用来识别和跟踪用户的一种方式。
mysqli_fetch_assoc ： 将返回内容变成一个关联数组
*/




<?php



if( isset( $_SESSION [ 'id' ] ) ) {
    // Get input 获取变量id
    $id = $_SESSION[ 'id' ];

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            // Check database 检测数据库
			// 查询语句 筛选条件user_id = '' 限制返回一条内容 LIMIT 1 
            $query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id' LIMIT 1;";
			// 返回内容  没有返回，输出Something went wrong
            $result = mysqli_query($GLOBALS["___mysqli_ston"], $query ) or die( '<pre>Something went wrong.</pre>' );

            // Get results 获取返回内容
            while( $row = mysqli_fetch_assoc( $result ) ) {
                // Get values	关联数组取值方式
                $first = $row["first_name"];
                $last  = $row["last_name"];

                // Feedback for end user  返回用户内容
                echo "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
            }
			//关闭数据库连接
            ((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);        
            break;
        case SQLITE:
            global $sqlite_db_connection;

            $query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id' LIMIT 1;";
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