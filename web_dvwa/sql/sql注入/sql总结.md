# sql注入的流程：

1、寻找客户端与服务器交互点

2、探测是否存在漏洞：

​		2.1基于错误的判断（数据库返回错误信息）

​		2.2基于布尔值的判断（and or & ||，根据错误返回判断是字符类型还是数字类型）

​		2.3基于联合查询的判断：union select

​		2.4基于时间的判断：（sleep时间函数，观察与正常输入返回的时间差）

​		2.5基于栈叠堆的判断（；  用分号，将前面语句结束掉，执行下一个语句）

3、探寻最大显示位：

​		3.1 order by 排序方式。不断的修改排序的最大字段数，寻找一个报错与不报错的临界点

​				1' order by 3 #

​		3.2 union select 联合查询方式，不断的增加显示字段数，当显示的最大字段数，吻合时，不报错。

​				1' union select 1, 2, 3#

4、通过显示位，输出各种函数内容（根据获取账号的权限）

​	数据库名称、数据库版本、当前用户名...



5、根据当前用数据库名称，探寻源数据库：information_schema

​		tables:存着数据库所用的库名称（table_schema字段）与表名称（table_name字段）

​		获取所有的表

​		统计每个数据库有多少张表

​		获取当前数据表名称

6、根据获取的数据库名称与表名称。获取数据表的字段名称

​	columns：存在数据库所有表的字段名称



7、根据表名称，获取字段内容。



# 位置：传递参数的两种方式：

请求的两种方式：get与post

get : 我们能直接在url上看到

post：url上看不到，我们可以用f12的网路，查看内容。或者使用工具查看内容



# 后端数据库查询语句猜测：

根据我们在第二步猜测，是否存在漏洞时，我们需要探测数当前查询语句是数字类型还是字符串类型。

数字类型与字符串类型的区别是：有无引号包裹

如：

```sql
数字类型：
select * from table where id= ;   # 没有引号包裹是数字类型

字符串类型：
select * from table where id = ' '; 有单引号包裹是字符串类型
或者
select * from table where id = " ";有双引号包裹是字符串类型
```



# 注释的使用技巧：

在sql中行间注释一个有三种： #、--+、%23(+号在浏览器url编码中表示空格)

行内注释/* */

当我们在url输入的时候，要避免使用#，#会被浏览器解释为收藏，不会起到我们想要的效果。这时我们应该使用url编码的%23。也就是#或者使用--+



# sql注入框架：

```sql
get请求框架:
http://192.168.8.20/dvwa/vulnerabilities/sqli? 参数 %23
我们现在只要在参数部分添砖加瓦就行啦


post请求框架：
http://192.168.8.20/dvwa/vulnerabilities/sqli/#
...
请求体
参数id = 1 %23

我们在请求体的参数部分添砖加瓦就行啦
```



# 常见参数

- `user()`：当前数据库用户
- `database()`：当前数据库名
- `version()`：当前使用的数据库版本
- `@@datadir`：数据库存储数据路径
- `concat()`：联合数据，用于联合两条数据结果。如 `concat(username,0x3a,password)`
- `group_concat()`：和 `concat()` 类似，如 `group_concat(DISTINCT+user,0x3a,password)`，用于把多条数据一次注入出来
- `concat_ws()`：用法类似
- `hex()` 和 `unhex()`：用于 hex 编码解码
- `load_file()`：以文本方式读取文件，在 Windows 中，路径设置为 `\\`
- `select xxoo into outfile '路径'`：权限较高时可直接写文件





# 编码转换

- `ASCII()`：返回字符的 ASCII 码值
- `CHAR()`：把整数转换为对应的字符



# 后台万能密码

- `admin' --`
- `admin' #`
- `admin'/*`
- `' or 1=1--`
- `' or 1=1#`
- `' or 1=1/*`
- `') or '1'='1--`
- `') or ('1'='1--`
- 以不同的用户登陆 `' UNION SELECT 1, 'anotheruser', 'doesnt matter', 1--`





# 安全策略分析与绕过：



## 绕过条件过滤

掌握了基本的注入手段。但是随着网站开发人员的安全意识的提高，纷纷使用了各种防注入的手段。最简单的就是条件过滤了。条件过滤，顾名思义就是黑名单机制，过滤掉符合条件的语句。因此我们要想办法绕过过滤，用其他方式来实现注入。

## 绕过伪静态

伪静态：伪静态通俗点说就是假的静态页面，也就是通过各种技术手段，让动态页面的URL看上去和静态页面的一样。

.htaccess文件是Apache服务器中的一个配置文件，它负责相关目录下的网页配置。

我们这里利用.htaccess文件，配置重写引擎，来实现伪静态的效果。

## 绕过函数过滤

为了保证用户输入的信息不会影响到程序进程，往往会对用户的输入进行处理。之前是对危险字符进行替换，但是有些字符的替换会影响用户的表达，那么就可以用 转义的方法对用户的输入符号进行完整保留。本实验有三个部分，第一部分是自定义转义函数，第二三部分都是用标准的addslashed()函数和 mysql_real_escape_string()来进行转义。

addslashes() 函数在指定的预定义字符前添加反斜杠。

这些预定义字符是：单引号 (')、双引号 (")、反斜杠 (\)、NULL

mysql_real_escape_string() 函数转义 SQL 语句中使用的字符串中的特殊字符。

下列字符受影响：`\x00、\n、\r、\、’、"、\x`1a

### 绕过is_numeric过滤

**is_numeric()函数用于检测变量是否为数字或数字字符串**。

如果将二进制或十六进制数据传递至is_numeric()函数，则也会返回为true，即被is_numeric()函数检测是数字。现在，我们进行测试。编写is_numeric.php文件，并将以下代码输入后，保存。
```php
<?php

    $v = is_numeric (0x32DA) ? true : false;
    
    var_dump ($v);

?>
```
再执行该程序，可得到结果：bool(true)。

vim is_numeric.php

php !$（!$代表上一条命令的最后一个参数，即is_numeric.php）

### 绕过条件过滤

关键代码为：

![img](https://i-blog.csdnimg.cn/blog_migrate/5c45caf340b2afdd36603fd2ae11471d.png)

​	

**首先看到GET方式，要习惯的打一个半角英文单引号**。我们这里打一下试试：

![img](https://i-blog.csdnimg.cn/blog_migrate/78883b94fa75b3a3db7d90c08a1589ed.png)

​		这里发现报错了，但是要是按照我们之前使用联合查询获取额外信息，就要注释掉后面的『’ LIMIT 0,1』，但是这里的代码却过滤掉了注释。那怎么办呢？我们这次试试输入『id=1’ or '』，看看可以用布尔方式不。

![img](https://i-blog.csdnimg.cn/blog_migrate/b645ebfc62545196c4d7c423ba22e0ec.png)

​		布尔方式成功，那我们就可以按照初级GET盲注中的布尔方式注入了。先试一下『 id=1' and (select DATABASE()='security') or'』

​		成功了。由于布尔按真假判断，那我们这里有三个判断，分别是id，and**和or’’，这里的or’’恒为真，当id也为真的时候，控制真假结果就取决于and的值，则符合布尔注入。

​		一般都SQL注释号为--和#这两个符号在入门SQL注入时用的比较多但是其实还有一个注释号;%00这个也可以用作注释。

![img](https://i-blog.csdnimg.cn/blog_migrate/09883f54561cdd23980893b04f83484a.png)

可以用爆破注入试一下,以下给出爆破注入的语句以及效果图

爆破注入『id=1' or extractvalue(1,concat(0x7e,database())) or'』

![img](https://i-blog.csdnimg.cn/blog_migrate/f515cfec2ff3fab660994d4f719b30d5.png)



关键代码为：

![img](https://i-blog.csdnimg.cn/blog_migrate/8f4f2d141a3467232d11e0882331c0fb.png)

​		这次还过滤了and和or，那我们就无法进行布尔判断了。此处可以使用替代的办法，即使用『&&』替代『and』，『||』替代『or』。此处还有个问题，就是preg_replace按照代码中的用法是无限次替换的，但实际中却可以使用『oorr』这样来绕过，这不科学。

![img](https://i-blog.csdnimg.cn/blog_migrate/3558066b591ec1c76d571e0e62ef7fe6.png)

关键代码为：

![img](https://i-blog.csdnimg.cn/blog_migrate/48343b87befa489be66095b7f83ae244.png)

 这和上面的差不多，但是这次使用『oorr』这样的方式试一下。

OORR来过滤因为过程OR会把中间两个OR去掉剩下的也是OR

![img](https://i-blog.csdnimg.cn/blog_migrate/c23ddd8452616bc7bbbe353a20de0bc6.png)

关键代码为：

![img](https://i-blog.csdnimg.cn/blog_migrate/70c63957249c4c7f17909a5e232a6934.png)

​		SQL语句中函数之间是要有空格的，如『and union』不能写成『andunion』，但是函数和变量中可以去掉空格，如『2 ＝ 1』可以写成『2=1』。这个黑名单屏蔽了空白字符以及斜杠星号，意味着用『/**/』伪装成空格的日子一去不复返了。因为空格只是隔开前后函数。

​		能看到空格和AND还有--都被过滤而我们可以用%20来代替空格,而||和&&这两符合都有自带的空格可以直接使用
![img](https://i-blog.csdnimg.cn/blog_migrate/42d024af10740825d69979a5a0168af5.png)

![img](https://i-blog.csdnimg.cn/blog_migrate/f2403d3303483b67ef42435e850a8444.png)



### 绕过UNION&SELECT过滤

关键代码为：

![img](https://i-blog.csdnimg.cn/blog_migrate/89be93e1f5d92af8ce65520aabda25c6.png)

与上一门课程相比，黑名单是越来越长了。我们随便试一下，提交id＝12211' union select * from users where id='2'||'，然后看一下过滤后还剩下什么了

![img](https://i-blog.csdnimg.cn/blog_migrate/0ad3e7f6686e54832f3abcd62dff730c.png)

过滤后惨不忍睹啊。我们再看看上面的代码，正则修饰符不是『i』而是『s』，则意味着匹配的对象是大小写敏感的，那么就简单多了呢。

但是空格和星号还是被吃掉了，继续想办法。在MySQL中tab，空格，回车都可以隔断语句，那么我们就可以用使用/**/ 或() 或+ 代替空格，%0c =换页、%09 = 水平制表符、%0d = 回车、%0a = 换行。那我们试试 『id=12211%27+uniOn%09selEct%0d1,2,password%0dfrom%0dusers%0dwhere%0did=%272%27||%27』
![img](https://i-blog.csdnimg.cn/blog_migrate/a60053659291720de6dc2db2955225c2.png)

成功了呢。很多不可见的空白字符都可以用哦，你可以试试。



### 绕过伪静态

.htaccess文件的配置如下：

![img](https://i-blog.csdnimg.cn/blog_migrate/c05c25af45a22e6883867d8d3fb4a4ad.png)

发现此处的地址栏是类似这样『Less-37/id/1』，目录形式，没有扩展名。那我们把这个1删除试试。发现报错

![img](https://i-blog.csdnimg.cn/blog_migrate/630e337c9b9fcb986f938782dfc60eb3.png)

在1后面添一个单引号咧，果断报错。

![img](https://i-blog.csdnimg.cn/blog_migrate/4fd4bf570a1cf25318684076f52a57a3.png)

 因此此处的注入点为数字1后面，并且是显错注入。



发现它打开的页面是『Less-38/id/1.html』。

![img](https://i-blog.csdnimg.cn/blog_migrate/d4886fd08e5c98dcf69855f6560a19cd.png)



1.html，貌似是静态网页啊，那我们将1改为2咧。

![img](https://i-blog.csdnimg.cn/blog_migrate/f03c1454d17b7bfd1601172045ceab58.png)



是正常页面，信息是ID=2人的用户信息。那么我们在后面打个单引号呢

![img](https://i-blog.csdnimg.cn/blog_migrate/c4d805a966bedbad1feda9699959357a.png)

原来这次的注入点在数字部分。





### 绕过函数过滤

关键代码为：

![img](https://i-blog.csdnimg.cn/blog_migrate/927b0f5c47e23531a4b4d205ce8e39b9.png)

![img](https://i-blog.csdnimg.cn/blog_migrate/2c1e524c0f43c020b7d834a54f87f99f.png)



本代码有三个过滤，第一个是过滤将反斜线替换为双反斜线。第二个和第三个分别是将单引号和双引号[转义](https://so.csdn.net/so/search?q=转义&spm=1001.2101.3001.7020)，即在引号前面添加反斜线。**有没有注意本数据库是gbk的**，那我们就可以进行『**宽字节**』注入了。

首先直接注入测试

![img](https://i-blog.csdnimg.cn/blog_migrate/79cd02545c0235fb767e39043b37eb5d.png)

发现单引号被转义了。我们在URL中插入的单引号之前插入%bf，单引号是%27。反斜线是%5c。『% bf \’』就变成了『 %bf%5c%27 』，相当于再显示就变成了『縗’』，单引号出来了。则最后的SQL语句就变成了『SELECT * FROM users WHERE id='-11縗'union select * from users where id =3-- ' LIMIT 0,1』因此突破了转义函数。

![img](https://i-blog.csdnimg.cn/blog_migrate/563abc5f7be0f8f666cda4ea956da02e.png)



怎么吃的：

   GBK编码，它的编码范围是0×8140~0xFEFE(不包括xx7F)，在遇到%df(ascii(223)) >ascii(128)时自动拼接%5c，因此吃掉‘\’，而%27、%20小于ascii(128)的字符就保留了。



绕过 AddSlashes() 函数, 这个和上一个程序一样，就是 『id=$id』，没有了单引号，那addslashes函数可以算是无用了。

![img](https://i-blog.csdnimg.cn/blog_migrate/eef7886a36d2b5d1ae3ea9fcdaabbc4c.png)



 绕过MySQL_real-escape_string函数,这里我们用的函数变成了mysql_real_escape_string，它比addslashes函数多转义了换行符、制表符等。理论上这个函数是很安全，而且能避免宽字节注入的。

![img](https://i-blog.csdnimg.cn/blog_migrate/3b7654d620360ef7cfc3daaf64e00321.png)



但我们依旧可以使用宽字符绕过。

SQL语句为『id=-1%bf%27+union+select+*+from+users+where+id+=3—+』

![img](https://i-blog.csdnimg.cn/blog_migrate/64f34f41c1f9f5beb79cf649a0497d4d.png)



### 绕过is_numeric过滤

访问目标网站，如下图所示：

![img](https://i-blog.csdnimg.cn/blog_migrate/9cd0297fd998c26c4b531c442d9c1ade.png)

通过查看源代码，在最后一行发现注释中包含“index.phps”文件，于是再访问该文件：http://10.1.1.8/index.phps，可查看到index.php的源代码：

![img](https://i-blog.csdnimg.cn/blog_migrate/763ddee09909c3b31a3419472ab9158c.png)

从源代码中可以分析出，服务器从POST获得id标签的值，首先利用is_numeric()函数判断id是否为数字或数字字符串，如果不是，则提示“hacking attemp（发现黑客尝试攻击行为）”，如下图所示。

![img](https://i-blog.csdnimg.cn/blog_migrate/a8d27a831b2d369e35dd01ca2985debf.png)

 **可以用十六进制或者二进制**绕过is_numeric，形成SQL注入。

绕过is_numeric()的方法：将SQL注入的payload转换为十六进制表示。

例如，payload为2’ and 1=1，则先选中该payload，利用Firefox中的hackbar工具，选择Encoding-->HEX Encoding-->String to 00ff00ff，即可完成十六进制转换，转换结果为322720616e6420313d31；接着，为了让PHP知道该字符串是十六进制数据，还需要在前面加上0x，最终变成0x322720616e6420313d31。

![img](https://i-blog.csdnimg.cn/blog_migrate/760316c5b3b823d1c0cda283f9d8981d.png)



 flag存在于mysql数据库的某个表中。尝试绕过is_numeric的限制实现SQL注入攻击，获取flag。

利用Firefox的hackbar工具，可以将payload进行十六进制转码。如下图所示，我们将payload=“-1 union all select database()”进行转码（注意：在转码后，还须在十六进制字符串前加上0x）实现SQL注入，获取到了数据库的名称：task

![img](https://i-blog.csdnimg.cn/blog_migrate/a45a3cfa2f913f3e650ac9813bb5adf1.png)

————————————————

原文链接：https://blog.csdn.net/weixin_43844217/article/details/123333481







# 绕过姿势与统计：

序号|	函数|	原理|	涉及字符| 	版本|	绕过方案|	备注
-|-|-|-|-|-|-
1|addslashes()|addslashes() 函数返回在预定<br/>义的字符前添加反斜杠的字符串|单引号（'）<br>双引号（"）<br>反斜杠（\）<br>NULL|4+|宽字符|
2|is_numeric函数|用于检查一个变量是否是数字<br/>或数字字符串（例如 "123" 或 "4.56"），<br/>如果是，则返回 `true`；否则返回 `false`。|数字/字符串/浮点型|PHP 4, PHP 5, PHP 7|**可以用十六进制或者二进制**绕过is_numeric|-
3|mysql_real_escape_string|用于转义字符串中的特殊字符;会考虑当前数据库连接的字符集|以下字符前添加反斜杠：`\x00`、`\n`、`\r`、`\`、`'`、`"` 和 `\x1a`|5.5-7|可以根据当前数据库，字符集。使用宽字符绕过|-
4|mysqli_real_escape_string|用于转义字符串中的特殊字符；根据当前连接的字符集来转义字符串|NUL（ASCII 0） 换行（`\n`） 回车（`\r`） 反斜杠（`\`） 单引号（`'`） 双引号（`"`） 控制字符 Z（`\x1a`）|7+|无法使用宽字符绕过，如果绕过单双引号，用16进制绕过|-
5||-|-|-|-|-
-|-|-|-|-|-|-
-|-|-|-|-|-|-



## addslashes()函数

### 定义和用法

addslashes() 函数返回在预定义的字符前添加反斜杠的字符串。

预定义字符是：

- 单引号（'）
- 双引号（"）
- 反斜杠（\）
- NULL

**提示：**该函数可用于为存储在数据库中的字符串以及数据库查询语句准备合适的字符串。

**注释：**默认情况下，PHP 指令 magic_quotes_gpc 为 on，对所有的 GET、POST 和 COOKIE 数据自动运行 addslashes()。不要对已经被 magic_quotes_gpc 转义过的字符串使用 addslashes()，因为这样会导致双层转义。遇到这种情况时可以使用函数 get_magic_quotes_gpc() 进行检测。

### 语法
```php
string addslashes ( string $str )
```

### 参数
- `$str`：要转义的字符串。

### 返回值
- 返回转义后的字符串。

### 示例
```php
$str = "It's a nice day.";
$safe_str = addslashes($str);
echo $safe_str; // 输出：It\'s a nice day.
```

在这个例子中，`addslashes()` 函数在字符串中的单引号前面添加了反斜杠，使其在数据库查询中可以安全使用。

### 注意事项
1. **安全性**：尽管 `addslashes()` 可以防止某些类型的 SQL 注入，但它不是最安全的转义方法。更推荐的做法是使用参数化查询（预处理语句）或 `mysqli_real_escape_string()` 函数。
2. **双引号**：`addslashes()` 也会转义双引号，这在某些情况下可能不是必要的，因为 SQL 语句通常使用单引号来包围字符串。
3. **其他特殊字符**：`addslashes()` 只转义引号和反斜杠，不会转义其他特殊字符，如 NUL 字符或其他可能影响 SQL 语句的特殊字符。

### 替代方法
- **参数化查询**：使用预处理语句和参数绑定来避免 SQL 注入。
- **`mysqli_real_escape_string()`**：在 MySQLi 扩展中，这个函数可以转义字符串中的特殊字符，使其安全用于 SQL 查询。

### 技术细节

| 返回值：   | 返回已转义的字符串。 |
| :--------- | -------------------- |
| PHP 版本： | 4+                   |

### 总结
`addslashes()` 是一个方便的函数，用于转义字符串中的引号和反斜杠，但它不应该作为防止 SQL 注入的主要手段。在现代 PHP 应用程序中，应该优先考虑使用更安全的数据库交互方法。

### 绕过姿势

**使用宽字符绕过**



GBK占用两字节
ASCII占用一字节
PHP中编码为GBK，函数执行添加的是ASCII编码，MySQL默认字符集是GBK等宽字节字符集。

%DF'：会被PHP当中的addslashes函数转义为"%DF"，"“既URL里的“%5C”，那也就是说，“%DF”会被转成“%DF%5C%27”倘若网站的字符集是GBK，MySQL使用的编码也是GBK的话，就会认为“%DF%5C%27”是一个宽字节。也就是”運'"
URL在线解密工具链接:

宽字符转换网址：https://tool.chinaz.com/tools/urlencode.aspx





## is_numeric函数

在 PHP 中，`is_numeric()` 函数用于检查一个变量是否是数字或数字字符串（例如 "123" 或 "4.56"），如果是，则返回 `true`；否则返回 `false`。

PHP 版本要求：PHP 4, PHP 5, PHP 7

### 语法
```php
bool is_numeric ( mixed $value )
```

### 参数
- `$value`：要检查的值。

### 返回值
- 如果 `value` 是数字或数字字符串，则返回 `true`，否则返回 `false`。注意浮点型返回 1，即 TRUE

### 示例
```php
$values = array("123", "4.56", "5e3", "123abc", null, true, false);

foreach ($values as $value) {
    if (is_numeric($value)) {
        echo "$value is a number.\n";
    } else {
        echo "$value is not a number.\n";
    }
}
```

在这个例子中，`is_numeric()` 会检查数组中的每个值是否是数字或数字字符串。数字字符串 "123"、"4.56" 和科学记数法 "5e3" 都会被识别为数字，而 "123abc" 不是有效的数字字符串，因此不会被识别为数字。布尔值 `true`（在 PHP 中等同于整数 1）和 `false`（等同于整数 0）也会被识别为数字。

### 注意事项
- `is_numeric()` 会认为布尔值 `true` 和 `false` 是数字，因为它们在算术上下文中分别等同于 1 和 0。
- 空字符串、NULL 和未定义的变量不会被认为是数字。
- 如果你只想检查变量是否是数值类型，而不想检查数字字符串，你可以使用 `is_float()` 或 `is_int()` 函数。

### 替代方法
- **`is_float()`**：检查变量是否是浮点数。
- **`is_int()`**：检查变量是否是整数。

### 总结
`is_numeric()` 是一个有用的函数，用于在更广泛的上下文中检查数字，包括数字字符串和布尔值。然而，如果你需要更精确地检查变量类型，可能需要结合使用其他类型检查函数。



### 绕过方案：

**可以用十六进制或者二进制**绕过is_numeric，形成SQL注入。

绕过is_numeric()的方法：将SQL注入的payload转换为十六进制表示





## mysql_real_escape_string函数

`mysql_real_escape_string()` 是 PHP 中的一个函数，它用于转义字符串中的特殊字符，以便安全地在 SQL 语句中使用。这个函数会考虑当前数据库连接的字符集，因此它可以确保数据在 MySQL 数据库中正确地存储和查询。

**语法**：
```php
string mysql_real_escape_string ( string $unescaped_string [, resource $link_identifier] )
```

**参数**：
- `unescaped_string`：需要转义的字符串。
- `link_identifier`（可选）：MySQL 连接的资源句柄。如果没有提供，将使用最近的连接。

**返回值**：
- 成功时返回转义后的字符串，失败时返回 `false`。

**转义规则**：
- `mysql_real_escape_string()` 会在以下字符前添加反斜杠：`\x00`、`\n`、`\r`、`\`、`'`、`"` 和 `\x1a`。

**使用示例**：
```php
$link = mysql_connect('localhost', 'username', 'password');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
$unsafe_string = "O'Reilly's";
$safe_string = mysql_real_escape_string($unsafe_string, $link);
echo $safe_string; // 输出：O\'Reilly\'s
```

**注意事项**：
1. `mysql_real_escape_string()` 函数在 PHP 5.5.0 后已被废弃，并在 PHP 7.0.0 中被移除。建议使用 `mysqli_real_escape_string()` 或 PDO 的 `quote()` 方法。
2. 使用此函数之前，必须确保已经建立了数据库连接。
3. 此函数不会转义 `%` 和 `_` 符号，这些在 SQL 中是 `LIKE` 语句的通配符。

**替代方案**：

- 如果你的项目需要在不连接数据库的情况下转义字符串，可以使用 `addslashes()` 函数，但请注意，它不会考虑数据库的字符集。
- 为了避免 SQL 注入，最佳实践是使用预处理语句（Prepared Statements）。

引用自 。

**版本 5.5-7**



## mysqli_real_escape_string() 

`mysqli_real_escape_string()` 是 PHP 中 MySQLi 扩展提供的函数，用于转义字符串中的特殊字符，以便安全地在 SQL 语句中使用。这个函数会根据当前连接的字符集来转义字符串，确保字符串可以安全地用于 SQL 查询。

函数的语法如下：
```php
string mysqli_real_escape_string ( mysqli $connection , string $escapestr ) : string
```

参数：
- `$connection`：必需。规定要使用的 MySQL 连接。
- `$escapestr`：必需。要转义的字符串。

返回值：
- 成功时返回转义后的字符串，失败时返回 `false`。

转义的字符包括：
- NUL（ASCII 0）
- 换行（`\n`）
- 回车（`\r`）
- 反斜杠（`\`）
- 单引号（`'`）
- 双引号（`"`）
- 控制字符 Z（`\x1a`）

使用示例：
```php
$mysqli = new mysqli("localhost", "my_user", "my_password", "world");
$city = "'s-Hertogenbosch";
$query = sprintf("SELECT CountryCode FROM City WHERE name='%s'",
$mysqli->real_escape_string($city));
$result = $mysqli->query($query);
printf("Select returned %d rows.\n", $result->num_rows);
```

如果你需要在没有连接到数据库的情况下转义字符串，可以使用 `addslashes()` 函数，但请注意，它不会考虑字符集，可能不适合多字节字符集。更安全的替代方案是使用 `preg_replace()` 函数，如下所示：
```php
function mres($value)
{
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
    return str_replace($search, $replace, $value);
}
```

请确保在将数据插入数据库之前使用 `mysqli_real_escape_string()` 或其他安全的转义方法，以防止 SQL 注入攻击。



**绕过姿势：**

进制转换网站：

https://www.sojson.com/hexadecimal.html

单双引号无法使用，可以将字号转为16进制绕过

```
'users'  十六进制：0x7573657273
```

