SELECT s1.stamp, s1.temperature as t1, s2.temperature as t2, s3.temperature as t3 
FROM logger.`2018-03-s1` as s1
join logger.`2018-03-s2` as s2 on s1.stamp = s2.stamp
join logger.`2018-03-s3` as s3 on s1.stamp = s3.stamp
order by stamp asc;
#where tcd.f_title like '%capannone%';