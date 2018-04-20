SELECT s1.stamp, s1.humidity as h1, s2.humidity as h2, s3.humidity as h3, s4.humidity as h4, s5.humidity as h5, s6.humidity as h6, s7.humidity as h7
FROM `2018-s1` as s1
join `2018-s2` as s2 on s1.stamp = s2.stamp
join `2018-s3` as s3 on s1.stamp = s3.stamp
join `2018-s4` as s4 on s1.stamp = s4.stamp
join `2018-s5` as s5 on s1.stamp = s5.stamp
join `2018-s6` as s6 on s1.stamp = s6.stamp
join `2018-s7` as s7 on s1.stamp = s7.stamp
where s1.stamp > 1521955427
order by s1.stamp desc;
#limit 50000;