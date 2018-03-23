SELECT s1.stamp, s1.temperature as t1, s2.temperature as t2, s3.temperature as t3 
FROM `2018-s1` as s1
join `2018-s2` as s2 on s1.stamp = s2.stamp
join `2018-s3` as s3 on s1.stamp = s3.stamp
join `2018-s4` as s4 on s1.stamp = s4.stamp
join `2018-s5` as s5 on s1.stamp = s5.stamp
where s1.stamp > 1521952667 and s1.stamp < 1521955427
order by s1.stamp asc;
#limit 50000;
