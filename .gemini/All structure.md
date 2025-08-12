<!------------------------------------------------------------------------------------
  INSERT INTO `mlangprintauto_transactioncate` (`no`, `Ttable`, `BigNo`, `title`, `TreeNo`) VALUES
(802, 'inserted', '0', '칼라인쇄(CMYK)', ''),
(823, 'inserted', '802', 'B4(8절) 257x367', ''),
(821, 'inserted', '802', 'A4 (210x297)', ''),
(714, 'inserted', '', '120g아트지,스노우지(독판인쇄)', '802'),
(808, 'inserted', '', '100모조', '802'),
(275, 'NameCard', '0', '일반명함(쿠폰)', ''),
(278, 'NameCard', '0', '고급수입지', ''),
(704, 'NameCard', '0', '카드명함(PET명함)', ''),
(276, 'NameCard', '275', '칼라코팅', ''),
(993, 'NameCard', '278', '몽블랑240g', '');
(706, 'NameCard', '704', '실버', ''),
(590, 'LittlePrint', '0', '소량포스터', ''),
(610, 'LittlePrint', '', '국2절', '590'),
(680, 'LittlePrint', '590', '100모조', ''),
(604, 'LittlePrint', '590', '120아트/스노우', ''),
(679, 'LittlePrint', '590', '80모조', ''),
(466, 'envelope', '0', '대봉투', ''),
(473, 'envelope', '466', '대봉투330*243(120g모조)', ''),
(920, 'envelope', '282', '쟈켓(100모조 220*105)', ''),
(283, 'envelope', '282', '소봉투(100모조 220*105)', ''),
(697, 'cadarok', '691', '12페이지 중철(A4)', ''),
(699, 'cadarok', '', '150g(A/T,S/W)', '691'),
(691, 'cadarok', '0', '카다록,리플렛', ''),
(692, 'cadarok', '691', '24절(127*260)3단', ''),
(614, 'MerchandiseBond', '0', '상품권(148x68', ''),
(615, 'MerchandiseBond', '614', '인쇄만', ''),
(616, 'MerchandiseBond', '614', '홀로그램박', ''),
(742, 'msticker', '0', '자석스티커(종이자석)', ''),
(743, 'msticker', '742', '90x60mm(후면에작은자석)', ''),
(261, 'msticker', '260', '50X50mm익일배송', ''),
(475, 'NcrFlambeau', '0', '양식(100매철)', ''),
(476, 'NcrFlambeau', '0', 'NCR 2매(100매철)', ''),
(477, 'NcrFlambeau', '0', 'NCR 3매(150매철)', ''),
(484, 'NcrFlambeau', '475', '계약서(A4).기타서식(A4)
(954, 'NcrFlambeau', '475', '거래명세표A4 100모조 중앙미싱 2도인쇄', ''),
(484, 'NcrFlambeau', '475', '계약서(A4).기타서식(A4)', ''),
(505, 'NcrFlambeau', '', '1도', '475'),
(682, 'NcrFlambeau', '676', '16절', ''),
내용의 일부만 있는것이고 실제는 db 의 테이블을 참조해야합니다

================================================
품목별 테이블
INSERT INTO `mlangprintauto_inserted` (`no`, `style`, `Section`, `quantity`, `money`, `TreeSelect`, `DesignMoney`, `POtype`, `quantityTwo`) VALUES
(158, '802', '821', 7, '640000', 714, 30000, '1', '28000'),
INSERT INTO `mlangprintauto_cadarok` (`no`, `style`, `Section`, `quantity`, `money`, `TreeSelect`, `DesignMoney`, `POtype`, `quantityTwo`) VALUES
(4, '691', '692', 1000, '268000', '699', '', '', ''),
INSERT INTO `mlangprintauto_envelope` (`no`, `style`, `Section`, `quantity`, `money`, `DesignMoney`, `POtype`) VALUES
(1, '282', '283', 1000, '35000', '5000', '1'),
INSERT INTO `mlangprintauto_littleprint` (`no`, `style`, `Section`, `quantity`, `money`, `TreeSelect`, `DesignMoney`, `POtype`, `quantityTwo`) VALUES
(1, '590', '610', 10, '146000', '679', '110000', '1', ''),
INSERT INTO `mlangprintauto_merchandisebond` (`no`, `style`, `Section`, `quantity`, `money`, `DesignMoney`, `POtype`) VALUES
(1, '614', '615', 500, '35000', '15000', '1'),
INSERT INTO `mlangprintauto_msticker` (`no`, `style`, `Section`, `quantity`, `money`, `DesignMoney`) VALUES
(2, '260', '261', 1000, '20000', '10000'),
INSERT INTO `mlangprintauto_namecard` (`no`, `style`, `Section`, `quantity`, `money`, `DesignMoney`, `POtype`) VALUES
(1, '275', '276', 500, '9000', '5000', '1'),
INSERT INTO `mlangprintauto_ncrflambeau` (`no`, `style`, `Section`, `quantity`, `money`, `TreeSelect`, `DesignMoney`, `POtype`, `quantityTwo`) VALUES
(11, '475', '484', 60, '140000', '505', '10000', '', ''),

내용의 일부만 있는것이고 실제는 db 의 테이블을 참조해야합니다

저장될때 테이블
INSERT INTO `MlangOrder_PrintAuto` (`no`, `Type`, `ImgFolder`, `Type_1`, `money_1`, `money_2`, `money_3`, `money_4`, `money_5`, `name`, `email`, `zip`, `zip1`, `zip2`, `phone`, `Hendphone`, `delivery`, `bizname`, `bank`, `bankname`, `cont`, `date`, `OrderStyle`, `ThingCate`, `pass`, `Gensu`, `Designer`) VALUES
(2907, '전단지', '', '\n\n\n\n\n', '', '', '', '', '', '비젼', '', '', '', '', '', '', NULL, '', '', '', '', '2008-03-29 00:00:00', '7', '전단지NC.bmp', '', 0, NULL),
(2910, '스티카', '', '\n\n\n\n\n', '', '', '', '', '', '은퇴농장', '', '', '', '', '', '', NULL, '', '', '', '', '2008-03-31 00:00:00', '7', '열무교정.jpg', '', 0, NULL), Add Rules to this file or a short description and have Kiro refine them for you:   
-------------------------------------------------------------------------------------> 