SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `alumni_placements`;


CREATE TABLE `alumni_placements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(100) NOT NULL,
  `student_email` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `batch_year` year(4) DEFAULT NULL,
  `company_name` varchar(200) NOT NULL,
  `job_title` varchar(200) NOT NULL,
  `salary` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `placement_date` date DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company` (`company_name`),
  KEY `idx_batch_year` (`batch_year`),
  KEY `idx_placement_date` (`placement_date`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `alumni_placements` VALUES('1','Harshal Prashant Runwal','harshal.runwal21@vit.edu','BTech-CS-A','2025','Chedo Tech','Industry Internship','NA','','2025-07-14',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('2','Shubhan Sarang Punde','shubhan.punde22@vit.edu','BTech-CS-A','2026','RNS Technology Services','Industry Internship','Rs.10000 per month','','2025-07-14',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('3','Ram Santosh Dorak','ram.dorak22@vit.edu','BTech-CS-A','2026','Calfus','Industry Internship','25000','','2025-06-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('4','Vedangi abhijit kulkarni','Vedangi.kulkarni22@vit.edu','TY-CS-D','2026','Tech Mahindra','Industry Internship','NA','','2025-06-02',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('5','Devesh Garg','devesh.garg22@vit.edu','BTech CSA','2026','Tata  Consultancy Services','Industry Internship','NA','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('6','Aarya Jitendra Gavaskar','aarya.gavaskar22@vit.edu','Btech-CS-A','2026','Spieretech','Industry Internship','25000','','2025-06-01',NULL,'','International Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('7','Janhavi Shounak Awere','janhavi.awere22@vit.edu','TY-CS-A','2026','XpressBees (Busybees Logistics Solutions Private Limited)','Industry Internship','Rs. 10000/- per month','','2025-06-19',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('8','Mayank Jitendra Ahuja','mayank.ahuja22@vit.edu','BTech-CS-C','2026','Forbes Marshall Pvt. Ltd.','Industry Internship','14,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('9','Elton Joseph Lobo','eltonjoseph.lobo22@vit.edu','BTECH-CS-A','2026','Midal Cables Bahrain B.S.C','Industry Internship','NA','','2025-06-29',NULL,'','International Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('10','Nikita Vinayak Supekar','nikita.supekar22@vit.edu','BTech-CS-A','2026','Wolters Kluwer','Industry Internship','40,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('11','Ahmad Ali Sayyed','ahmadali.sayyed22@vit.edu','BTech-CS-A','2026','CNS Technologies FZCO','Industry Internship','RS. 58261','','2025-08-01',NULL,'','International Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('12','Revati Jitendra Shimpi','revati.shimpi22@vit.edu','Btech-CS-A','2026','SAS','Industry Internship','25,000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('13','Durva Rajesh Ajgaonkar','durva.ajgaonkar22@vit.edu','TY-CS-A','2026','PTC Software India','Industry Internship','22000/-','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('14','Shreya Prakash Bambal','shreya.bambal22@vit.edu','BTech-CS-A','2026','IBM India Private Limited','Industry Internship','30,000','','2025-08-05',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('15','Nachiket Pramod Rakhonde','nachiket.rakhonde22@vit.edu','BTech-CS-A','2026','SailPoint Technology','Industry Internship','25000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('16','Hriday Rajesh Badani','hriday.badani22@vit.edu','BTech-CS-A','2026','Softolytics Private Limited','Industry Internship','1500','','2025-06-02',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('17','Samarveer Sushant Moray','samarveer.moray22@vit.edu','TY-CS-D','2026','Wolters Kluwer','Industry Internship','40000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('18','Jannu','undefined.jannu22@vit.edu','BTECH-CS-A','2026','IITM','Research Internship','NA','','2025-07-20',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('19','Samarth Rakesh Otari','samarth.otari22@vit.edu','BTech-CS-A','2026','SAS Research and Development','Industry Internship','25000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('20','Prasad-Nandkishor-Ingle','prasad.ingle22@vit.edu','BTech-CS-B','2026','Redaptive','Industry Internship','20000','','2025-06-09',NULL,'','International Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('21','Shaunak Shirish Durani','shaunak.durani22@vit.edu','BTech-CS-A','2026','Wolters Kluwer','Industry Internship','40000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('22','Om Ravindra Shintre','om.shintre22@vit.edu','BTech-CS-A','2026','CrowdStrike','Industry Internship','30000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('23','Shreyas Sanjay Done','shreyas.done22@vit.edu','BTech-CS-A','2026','Morgan Stanley','Industry Internship','87000','','2025-08-11',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('24','Aditya Shirish Deore','aditya.deore22@vit.edu','BTech-CS-A','2026','IBM','Industry Internship','30,000','','2025-07-27',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('25','Aniket Anil Kothawade','aniket.kothawade22@vit.edu','CS-B','2022','Semtech','Industry Internship','35000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('26','Yash Santosh Paryani','yash.paryani22@vit.edu','BTech-CS-C','2026','Morgan Stanley Capital International (MSCI)','Industry Internship','60000','','2025-06-16',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('27','Ayush Kiran Khambayate','ayush.khambayate22@vit.edu','TY-CS-B','2026','Whirlpool','Industry Internship','25000','','2025-08-04',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('28','Keyur Vivek pande','keyur.pande22@vit.edu','BTech CS A','2026','Calfus Technologies','Industry Internship','25,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('29','Ayush Sachin Laddha','laddha.ayush22@vit.edu','TY-CS-A','2026','Datasmith Ai','Industry Internship','NA','','2025-06-02',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('30','Aditya Anupkumar Sakhare','aditya.sakhare22@vit.edu','Btech-CS-A','2026','Infineon Technologies, India','Industry Internship','39000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('31','Swami Chandrakant Patil','swami.patil22@vit.edu','BTech-CS-A','2026','Osmos.ai','Industry Internship','20,000','','2025-07-23',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('32','Saloni Anil Khandelwal','saloni.khandelwal22@vit.edu','Btech- CS-A','2026','IBM','Industry Internship','30,000','','2025-08-05',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('33','Ved-Anil-Mundhe','anil.ved22@vit.edu','TY-CS-D','2026','Quantiphi','Industry Internship','22000','','2025-07-14',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('34','Sejal Shrikrushna Hage','sejal.hage22@vit edu','BTech-CS-A','2026','SailPoint','Industry Internship','25000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('35','Aneesh Surendra Oak','aneesh.oak22@vit.edu','TY-CS-C','2026','Lattice Semiconductors, Pune (LPQ)','Industry Internship','35000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('36','Nikita Vinayak Pawar','nikita.pawar22@vit.edu','BTech CS-A','2026','Syngenta','Industry Internship','30000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('37','Advay Sachin Argade','advay.argade22@vit.edu','BTech-CS-A','2026','Morgan Stanley','Industry Internship','87000','','2025-08-11',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('38','Atharva Ajay Bonde','atharva.bonde22@vit.edu','BTech-CS-A','2026','Geekenized Technologies','Industry Internship','20,000','','2025-08-11',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('39','Aniket Jitendra Kalbhor','aniket.kalbhor22@vit.edu','BTech-CS-A','2026','SaS R&D','Industry Internship','25000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('40','Karan Aditya Harshey','karan.harshey22@vit.edu','BTech CS-A','2026','PTC','Industry Internship','22000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('41','Suyash Tanaji Gaikwad','suyash.gaikwad22@vit.edu','BTech-CS-B','2026','Markytics.AI','Industry Internship','10000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('42','Parth Prashant Kedari','parth.kedari22@vit.edu','BTech-CS--A','2026','Vishwakarma Institute of Technology, Pune','Research Internship','NA','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('43','Anushka Suresh Varpe','anushka.varpe22@vit.edu','BTech-CS-A','2026','Crowdstrike','Industry Internship','30,000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('44','Tejasvini Vijaykumar Wagh','tejasvini.wagh22@vit.edu','BTech-CS-A','2026','Semtech','Industry Internship','35000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('45','Rahul Avinash Sakpal','rahul.sakpal22@vit.edu','BTech-CS-A','2026','EUSPACE Technologies Pvt. Ltd.','Industry Internship','NA','','2025-07-10',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('46','Shrey Arjun Chougule','arjun.shrey22@vit.edu','BTech-CS-A','2026','Research Internship','Research Internship','0','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('47','Akhil Ashok Mate','akhil.mate22@vit.edu','TY-CS-A','2026','Markytics.AI','Industry Internship','10000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('48','Arya Jagdish Rajvaidya','arya.rajvaidya22@vit.edu','BTech-CS-A','2026','Infineon Technologies','Industry Internship','39000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('49','Moin Usman Khan','khan.moin22@vit.edu','BTech-CS-A','2026','PTC','Industry Internship','22000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('50','Rachit Dharmendra Nimje','rachit.nimje22@vit.edu','BTech-CS-A','2026','Siemens Digital Industry Software','Industry Internship','40000','','2025-06-02',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('51','Shivendra Mahadev Jadhav','shivendra.jadhav22@vit.edu','BTech-CS-A','2026','PTC Software (India) Pvt. Ltd.','Industry Internship','22,000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('52','Vedant Bharat Mohol','vedant.mohol22@vit.edu','BTech-CS-A','2026','Onlinesales.ai','Industry Internship','20000','','2025-07-23',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('53','Pornima Pravin Dokhale','pornima.dokhale22@vit.edu','BTech-CS-A','2026','Vishwakarma Institute of Technology','Research Internship','NA','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('54','Renuka Rajesh Pawar','renuka.pawar22@vit.edu','BTech-CS-D','2026','Fibe (formerly EarlySalary)','Industry Internship','12000','','2025-06-02',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('55','Vanshika Dattakumar Dongare','vanshikaq.dongare22@vit.edu','BTech-CS-A','2026','SAS R&D','Industry Internship','25,000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('56','Sagar Prakash Patil','sagar.patil22@vit.edu','BTech-CS-A','2026','Osmos.ai','Industry Internship','20000','','2025-07-23',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('57','Ashumal Laxman Palde','ashumal.palde22@vit.edu','BTech-CS-A','2026','Vishwakarma Institute of Technology, Pune','Research Internship','NA','','2025-07-11',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('58','Abheerav Kaushal Patankar','abheerav.patankar221@vit.edu','TY-CS-C','2025','NVIDIA Graphics Pvt. Ltd.','Industry Internship','80000','','2025-07-14',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('59','Ashutosh Santosh Kshirsagar','ashutosh.kshirsagar22@vit.edu','TY-CS-B','2026','Colgate Palmolive Private Limited','Industry Internship','25000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('60','Subodh Sunil Humne','subodh.humne221@vit.edu','BTech-CS-A','2025','IIT Hyderabad','Research Internship','12300 - 16000','','2025-08-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('61','Yash Shripad Bhalerao','yash.bhalerao221@vit.edu','BTech-CS-A','2025','Forma.ai','Industry Internship','40000','','2025-06-02',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('62','Soham Chandrashekhar Joshi','soham.joshi221@vit.edu','TY-CS-B,BTech-CS-A','2025','Invisalign India LLP','Industry Internship','50000','','2025-06-10',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('63','Atharv Jayant Bapat','atharv.bapat221@vit.edu','BTech-CS-A','2025','Semtech','Industry Internship','35000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('64','Janhavi Ajit Rajurkar','janhavi.rajurkar22@vit.edu','BTech-CS-A','2026','IBM Systems Lab','Industry Internship','30,000','','2025-08-05',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('65','Gayatri Deepak Dhumal','gayatri.dhumal221@vit.edu','BTech-CS-A','2025','SAS Research and Development (SAS R&D)','Industry Internship','25000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('66','Chirag Deepak Belani','chirag.belani22@vit.edu','TY-CS-A','2026','Calfus Inc.','Industry Internship','25k','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('67','Vishwaraj Sandeep Ingawale','vishwaraj.ingawale@latticesemi.com','TY-CS-B',NULL,'Lattice semiconductors','Industry Internship','35,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('68','Nauman Jakir Tamboli','nauman.tamboli221@vit.edu','BTech-CS-A','2025','Calfus Inc','Industry Internship','25,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('69','Sahil Amar Patil','sahil.patil221@vit.edu','BTech-CS-A','2025','PTC Software (India) Pvt. Ltd.','Industry Internship','22000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('70','Soham-Nilesh-Kasurde','soham.kasurde221@vit.edu','BTech-CS-A','2025','Calfus Technologies','Industry Internship','25,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('71','Harshita Yadav','yadav.harshita22@vit.edu','BTech-CS-A','2026','Herbs Magic','Industry Internship','NA','','2025-08-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('72','Amay - Patel','amay.patel22@vit.edu','BTech-CS-A','2026','Tech Mahindra','Industry Internship','NA','','2025-06-04',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('73','Daksh_Saklani','daksh.saklani22@vit.edu','BTech-CS-A','2026','Wipro','Industry Internship','NA','','2025-07-08',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('74','Atharva Rajesh Nehete','atharva.nehete221@vit.edu','BTech-CS-A','2025','SAS R&D','Industry Internship','25000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('75','Lakshya-Yashpal-Singh','lakshya.singh222@vit.edu','TY-CS-D','2026','SOFTBYTE INDIA PVT LTD','Industry Internship','10000','','2025-07-18',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('76','Sarthak Jagannath Gadekar','sarthak.gadekar221@vit.edu','Btech-CS-A','2025','PTC','Industry Internship','22,000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('77','Gaurav Rajkumar Sulsule','gaurav.sulsule221@vit.edu','BTech-CS-B','2025','Nvidia','Industry Internship','80000','','2025-07-14',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('78','Parth Manoj Mahajan','parth.mahajan221@vit.edu','TY-CS-C, BTech-CS-A','2025','Colgate Global Business Services Private Limited  (CGBS)','Industry Internship','25000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('79','Sneha Jain','sneha.jain221@vit.edu','BTech-CS-D','2025','Seagate','Industry Internship','30,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('80','Tarun Mukesh Kasliwal','tarun.kasliwal22@vit.edu','TY-CS-B','2026','Morgan Stanley','Industry Internship','87000','','2025-08-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('81','SUYASH SUBODH SAWANT','suyash.sawant22@vit.edu','BTech-CS-D','2026','UXLI Ltd','Industry Internship','NA','','2025-06-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('82','Dhyey Nikunj Thakkar','dhyey.thakkar22@vit.edu','BTech CS-A','2026','Relevance Lab','Industry Internship','NA','','2025-07-15',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('83','Tanvi Dharmendra Gunjal','tanvi.gunjal22@vit.edu','BTech-CS-A','2026','Vishwakarma Institute of Technology, Pune','Research Internship','NA','','2025-07-10',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('84','Palaash Rahul Padman','palaash.padman22@vit.edu','BTech-CS-A','2026','TechBulls SoftTech Pvt. Ltd','Industry Internship','NA','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('85','Aastha Roshan Jain','aastha.jain22@gmail.com','BTech-CS-B','2026','Actin Technologies','Industry Internship','Rs. 7000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('86','Krushna Ramdas Gore','krushna.gore221@vit.edu','BTech-CS-A','2025','Wolters Kluwer India Pvt Ltd','Industry Internship','40000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('87','Soham Balasaheb Gargote','balasaheb.soham221@vit.edu','CS-D','2021','WhileOne Techsoft','Industry Internship','10,000','','2025-06-09',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('88','Shrishti Avinash Kenjale','shrishti.kenjale22@vit.edu','BTech-CS-A','2026','PricewaterhouseCoopers(PwC)','Industry Internship','20000','','2025-08-04',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('89','Atharva Sunil Chawle','atharva.chawle221@vit.edu','TY-CS-A','2025','Wolters kluwer','Industry Internship','40,000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('90','Vivek Prashant Nikam','vivek.nikam23@vit.edu','BTech-CS-A','2027','Juspay Technologies Private Limited','Industry Internship','30000','','2025-06-09',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('91','Mandar Pravin Pandagale','mandar.pandagale23@vit.edu','BTech-CS-A','2027','fluxava','Industry Internship','NA','','2025-03-22',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('92','Yash Sandip Mahajan','yash.mahajan23@vit.edu','BTech-CS-A','2027','InsideWin Media Solutions Pvt Ltd','Industry Internship','15K','','2025-08-04',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('93','Prem Vijay Borse','prem.borse23@vit.edu','BTech-CS-A','2027','Colgate Palmolive','Industry Internship','25000','','2025-07-07',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('94','Sahil Manish Sinnarkar','sahil.sinnarkar23@vit.edu','BTech-CS-A','2027','Seagate Technology','Industry Internship','30000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('95','Prathmesh Sambhaji Deshmukh','prathmesh.deshmukh23@vit.edu','BTech-CS-A','2027','CakeSoft Technologies Private Limited','Industry Internship','20000','','2025-06-19',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('96','Aditya Sunil kadlag','aditya.kadlag23@vit.edu','TY-CS-A','2027','SoulSoft Infotech Pvt Ltd','Industry Internship','30000','','2025-07-25',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('97','Aditya Maruti Bhendavadekar','aditya.bhendavadekar23@vit.edu','BTech-CS-A','2027','SAS Research & Development (India) Pvt. Ltd.','Industry Internship','25000','','2025-07-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('98','Komal Mahadev Potdar','komal.potdar23@vit.edu','BTech-CS-A','2027','IBM','Industry Internship','30000','','2025-07-20',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('99','Devansh Arun Jaiswal','devansh.jaiswal23@vit.edu','BTech-CS-A','2027','Infineon','Industry Internship','39000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('100','Gauri Sanjay Barge','gauri.barge23@vit.edu','BTech-CS-A','2027','Wolters Kluwer','Industry Internship','40000','','2025-07-01',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('101','Suhasini Chandrakishor Choudhari','suhasini.choudhari23@vit.edu','BTech-CS-B','2027','Google','Industry Internship','Rs.1,35,000/-','','2025-06-03',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('102','SUYASH SUNIL DONGRE','suyash.dongre23@vit.edu','BTech-CS-A','2027','Google | Google Summer of Code 2025','Industry Internship','125000 (Total)','','2025-06-02',NULL,'','International Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('103','Rutika Vinay Bari','rutika.bari221@vit.edu','BTech-CS-A','2025','Siemens','Industry Internship','40,000','','2024-06-02',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('104','Shreya Jitendra Barsude','shreya.barsude221@vit.edu','TY-CS-A','2025','Osmos-onlinesales.ai','Industry Internship','20000','','2025-07-23',NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('105','MANASI RAMDAS KAMBLE','manasi.kamble22@vit.edu','BTech-Computer Engineering','2026','IBM Systems Lab','Industry Internship','30000','',NULL,NULL,'','National Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('106','Leevan Herald','leevan.herald22@vit.edu','BTech-Computer Engineering','2026','VIT','Research Internship','NA','','2025-07-07',NULL,'','','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('107','Atharva Ashish Gore','atharva.gore22@vit.edu','BTech-CS-A','2026','HanesBrands','Nguyen.ThanhTrung@hanes.com, Hasantha.Ariyawansa@hanes.com','18,000','','2025-12-22',NULL,'','Industry Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('108','Ambarish Animesh Singh','ambarish.singh22@vit.edu','BTech-CS-A','2026','Hanes brand tnc','Nguyen.ThanhTrung@hanes.com, Hasantha.Ariyawansa@hanes.com','18000','','2025-12-22',NULL,'','Industry Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('109','Rukmoddin Nabab Patel','rukmoddin.patel221@vit.edu','BTech-CS-B','2025','Securonix','Not assigned','50000','','2026-02-25',NULL,'','Industry Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('110','Bhagyesh Sunil Pawar','bhagyesh.pawar221@vit.edu','Btech CS A','2025','Securonix','Not assigned','50000','','2026-02-25',NULL,'','Industry Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('111','Nayana Vijaysingh Thakur','nayana.thakur22@vit.edu','BTech_CS_A','2026','Securonix India Pvt Ltd','Not Assigned','50000','','2026-02-25',NULL,'','Industry Internship','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('112','Siddhartha Dhurve','siddhartha.dhurve22@vit.edu','Btech_CS_A','2026','NIC','Industry Internship','','',NULL,NULL,'','','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('113','Aaditya Manish Patil','aaditya.patil22@vit.edu','BTECH-CS-A','2026','VIT','Research Internship','','',NULL,NULL,'','','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('114','Shriraj yamkanmardi','shriraj.yamkanmardi22@vit.edu','BTech-CS-A','2026','VIT','Research Internship','','',NULL,NULL,'','','2026-05-08 02:25:12','2026-05-08 02:25:12');
INSERT INTO `alumni_placements` VALUES('115','Sarthak Sanjay Pithe','sarthak.pithe22@vit.edu','Btech-CS-A','2026','Uptiq.AI','Industry Internship','25000','','2025-08-21',NULL,'','','2026-05-08 02:25:12','2026-05-08 02:25:12');



DROP TABLE IF EXISTS `alumni_students`;


CREATE TABLE `alumni_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `current_company` varchar(255) DEFAULT NULL,
  `current_position` varchar(255) DEFAULT NULL,
  `expertise_areas` text DEFAULT NULL,
  `is_available_for_chat` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_id`),
  KEY `idx_available` (`is_available_for_chat`),
  CONSTRAINT `alumni_students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `applications`;


CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `status` enum('pending','interview','accepted','rejected') DEFAULT 'pending',
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_application` (`student_id`,`job_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_job_id` (`job_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `applications` VALUES('1','3','6','accepted','2024-01-13 09:15:00');
INSERT INTO `applications` VALUES('2','4','4','pending','2024-01-16 11:45:00');
INSERT INTO `applications` VALUES('3','1','11','pending','2024-01-20 10:15:00');
INSERT INTO `applications` VALUES('4','2','12','interview','2024-01-19 14:50:00');
INSERT INTO `applications` VALUES('46','1','50','pending','2026-05-08 02:44:08');
INSERT INTO `applications` VALUES('47','1','36','pending','2026-05-08 02:45:27');
INSERT INTO `applications` VALUES('48','1','35','pending','2026-05-08 02:45:39');
INSERT INTO `applications` VALUES('49','1','37','pending','2026-05-08 02:45:46');
INSERT INTO `applications` VALUES('50','1','39','pending','2026-05-08 02:46:04');



DROP TABLE IF EXISTS `ats_scores`;


CREATE TABLE `ats_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `job_description` text DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `overall_score` int(11) NOT NULL,
  `keyword_match_percentage` decimal(5,2) NOT NULL,
  `missing_skills` text DEFAULT NULL,
  `suggestions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`suggestions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_resume_id` (`resume_id`),
  CONSTRAINT `ats_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ats_scores_ibfk_2` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`overall_score` >= 0 and `overall_score` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `chat_conversations`;


CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `junior_id` int(11) NOT NULL,
  `senior_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_conversation` (`junior_id`,`senior_id`),
  KEY `idx_junior` (`junior_id`),
  KEY `idx_senior` (`senior_id`),
  CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`junior_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_conversations_ibfk_2` FOREIGN KEY (`senior_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `chat_messages`;


CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `idx_conversation` (`conversation_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `companies`;


CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` VALUES('1','2','Google','company@gmail.com','Search Engine','Pune','1234567890','https://www.youtube.com/watch?v=G6IHWsqZnYg','qwerty','2026-05-07 18:53:02');
INSERT INTO `companies` VALUES('2','101','TechCorp Solutions','hr@techcorp.com','Information Technology','Bangalore',NULL,NULL,NULL,'2026-05-07 20:03:18');
INSERT INTO `companies` VALUES('3','2','DataViz Analytics','careers@dataviz.com','Data Analytics','Hyderabad',NULL,NULL,NULL,'2026-05-07 20:03:18');
INSERT INTO `companies` VALUES('4','3','CloudTech Systems','jobs@cloudtech.com','Cloud Computing','Mumbai',NULL,NULL,NULL,'2026-05-07 20:03:18');
INSERT INTO `companies` VALUES('12','101','TechCorp Solutions','hr@techcorp.com','Information Technology','Bangalore',NULL,NULL,NULL,'2026-05-07 20:03:31');
INSERT INTO `companies` VALUES('13','2','DataViz Analytics','careers@dataviz.com','Data Analytics','Hyderabad',NULL,NULL,NULL,'2026-05-07 20:03:31');
INSERT INTO `companies` VALUES('14','3','CloudTech Systems','jobs@cloudtech.com','Cloud Computing','Mumbai',NULL,NULL,NULL,'2026-05-07 20:03:31');
INSERT INTO `companies` VALUES('22','101','TechCorp Solutions','hr@techcorp.com','Information Technology','Bangalore',NULL,NULL,NULL,'2026-05-07 20:03:35');
INSERT INTO `companies` VALUES('23','2','DataViz Analytics','careers@dataviz.com','Data Analytics','Hyderabad',NULL,NULL,NULL,'2026-05-07 20:03:35');
INSERT INTO `companies` VALUES('24','3','CloudTech Systems','jobs@cloudtech.com','Cloud Computing','Mumbai',NULL,NULL,NULL,'2026-05-07 20:03:35');



DROP TABLE IF EXISTS `company_interviews`;


CREATE TABLE `company_interviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `company_user_id` int(11) NOT NULL,
  `interview_at` datetime NOT NULL,
  `mode` enum('online','onsite','phone') DEFAULT 'online',
  `meeting_link` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `result_status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_id` (`application_id`),
  KEY `idx_company_interview_at` (`company_user_id`,`interview_at`),
  CONSTRAINT `company_interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `company_interviews_ibfk_2` FOREIGN KEY (`company_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `company_interviews` VALUES('1','2','2','2026-05-07 21:28:00','online','https://www.youtube.com/watch?v=G6IHWsqZnYg','efghjk','scheduled','2026-05-07 20:04:38','2026-05-07 20:04:38');
INSERT INTO `company_interviews` VALUES('3','50','2','2026-05-09 18:06:00','online','Pune','','scheduled','2026-05-08 02:47:06','2026-05-08 02:47:06');



DROP TABLE IF EXISTS `company_notifications`;


CREATE TABLE `company_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_user_id` int(11) NOT NULL,
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company_unread` (`company_user_id`,`is_read`,`created_at`),
  CONSTRAINT `company_notifications_ibfk_1` FOREIGN KEY (`company_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `company_notifications` VALUES('1','2','Job Created','New job \"qwerty\" was posted.','success','0','2026-05-07 19:47:53');
INSERT INTO `company_notifications` VALUES('2','2','Job Created','New job \"szdxfcghvjbknlm\" was posted.','success','0','2026-05-07 19:48:23');
INSERT INTO `company_notifications` VALUES('3','2','Job Updated','Your job posting has been updated successfully.','success','0','2026-05-07 19:49:02');
INSERT INTO `company_notifications` VALUES('4','2','Interview Scheduled','Interview saved for application #2.','success','0','2026-05-07 20:04:38');
INSERT INTO `company_notifications` VALUES('5','2','Job Created','New job \"etsydfgkhkj\" was posted.','success','0','2026-05-08 02:09:00');
INSERT INTO `company_notifications` VALUES('6','2','Job Updated','Your job posting has been updated successfully.','success','0','2026-05-08 02:09:58');
INSERT INTO `company_notifications` VALUES('7','2','Interview Scheduled','Interview saved for application #2.','success','0','2026-05-08 02:34:47');
INSERT INTO `company_notifications` VALUES('8','2','Application Status Updated','Application #2 moved to pending.','info','0','2026-05-08 02:34:48');
INSERT INTO `company_notifications` VALUES('9','2','Job Updated','Your job posting has been updated successfully.','success','0','2026-05-08 02:38:38');
INSERT INTO `company_notifications` VALUES('10','2','Job Updated','Your job posting has been updated successfully.','success','0','2026-05-08 02:42:48');
INSERT INTO `company_notifications` VALUES('11','2','Interview Scheduled','Interview saved for application #50.','success','0','2026-05-08 02:47:06');
INSERT INTO `company_notifications` VALUES('12','2','Application Status Updated','Application #50 moved to pending.','info','0','2026-05-08 02:47:08');



DROP TABLE IF EXISTS `job_cache`;


CREATE TABLE `job_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `skills` text DEFAULT NULL,
  `jobs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`jobs`)),
  `cached_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `job_cache_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `jobs`;


CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `required_skills` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `salary` varchar(50) DEFAULT NULL,
  `status` enum('active','closed') DEFAULT 'active',
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_status` (`status`),
  KEY `idx_posted_at` (`posted_at`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `jobs` VALUES('3','1','qwerty','qwerty','ersdyfghibi','xtxduyviyv','wzxcgvhb','99999','active','2026-05-07 19:47:53','2026-05-07 19:47:53');
INSERT INTO `jobs` VALUES('4','1','szdxfcghvjbknlm','tfcgvhbjknm','xfchgvjbknm, ','wdxfchgvjbkn','aweesdtfcgvhbjn','888989','active','2026-05-07 19:48:23','2026-05-07 19:48:23');
INSERT INTO `jobs` VALUES('5','1','Senior Software Developer','Develop and maintain web applications using modern technologies','Java, Spring Boot, React, MySQL, 3+ years experience',NULL,'Bangalore','8-12 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('6','2','Data Scientist','Build machine learning models and analyze large datasets','Python, Machine Learning, SQL, Statistics, 2+ years experience',NULL,'Hyderabad','6-10 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('7','3','Cloud Engineer','Design and implement cloud infrastructure solutions','AWS, Docker, Kubernetes, Linux, 2+ years experience',NULL,'Mumbai','7-11 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('8','4','Frontend Developer','Create responsive web applications with modern frameworks','React, JavaScript, HTML, CSS, 1+ years experience',NULL,'Pune','4-7 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('9','1','Junior Software Developer','Entry-level position for fresh graduates','Java, Python, Basic programming knowledge',NULL,'Bangalore','3-5 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('10','2','Data Analyst Intern','Analyze data and create visualizations','Python, SQL, Excel, Fresh graduates welcome',NULL,'Hyderabad','2-4 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('11','3','Cloud Support Engineer','Provide technical support for cloud services','AWS basics, Linux, Customer support',NULL,'Mumbai','3-6 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('12','4','UI/UX Designer','Design user interfaces and user experiences','Figma, Adobe XD, Design principles',NULL,'Pune','4-7 LPA','active','2026-05-07 20:03:18','2026-05-07 20:03:18');
INSERT INTO `jobs` VALUES('20','1','Senior Software Developer','Develop and maintain web applications using modern technologies','Java, Spring Boot, React, MySQL, 3+ years experience',NULL,'Bangalore','8-12 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('21','2','Data Scientist','Build machine learning models and analyze large datasets','Python, Machine Learning, SQL, Statistics, 2+ years experience',NULL,'Hyderabad','6-10 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('22','3','Cloud Engineer','Design and implement cloud infrastructure solutions','AWS, Docker, Kubernetes, Linux, 2+ years experience',NULL,'Mumbai','7-11 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('23','4','Frontend Developer','Create responsive web applications with modern frameworks','React, JavaScript, HTML, CSS, 1+ years experience',NULL,'Pune','4-7 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('24','1','Junior Software Developer','Entry-level position for fresh graduates','Java, Python, Basic programming knowledge',NULL,'Bangalore','3-5 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('25','2','Data Analyst Intern','Analyze data and create visualizations','Python, SQL, Excel, Fresh graduates welcome',NULL,'Hyderabad','2-4 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('26','3','Cloud Support Engineer','Provide technical support for cloud services','AWS basics, Linux, Customer support',NULL,'Mumbai','3-6 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('27','4','UI/UX Designer','Design user interfaces and user experiences','Figma, Adobe XD, Design principles',NULL,'Pune','4-7 LPA','active','2026-05-07 20:03:31','2026-05-07 20:03:31');
INSERT INTO `jobs` VALUES('35','1','Senior Software Developer','Develop and maintain web applications using modern technologies','Java, Spring Boot, React, MySQL, 3+ years experience',NULL,'Bangalore','8-12 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('36','2','Data Scientist','Build machine learning models and analyze large datasets','Python, Machine Learning, SQL, Statistics, 2+ years experience',NULL,'Hyderabad','6-10 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('37','3','Cloud Engineer','Design and implement cloud infrastructure solutions','AWS, Docker, Kubernetes, Linux, 2+ years experience',NULL,'Mumbai','7-11 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('38','4','Frontend Developer','Create responsive web applications with modern frameworks','React, JavaScript, HTML, CSS, 1+ years experience',NULL,'Pune','4-7 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('39','1','Junior Software Developer','Entry-level position for fresh graduates','Java, Python, Basic programming knowledge','','Bangalore','3-5 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('40','2','Data Analyst Intern','Analyze data and create visualizations','Python, SQL, Excel, Fresh graduates welcome',NULL,'Hyderabad','2-4 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('41','3','Cloud Support Engineer','Provide technical support for cloud services','AWS basics, Linux, Customer support',NULL,'Mumbai','3-6 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('42','4','UI/UX Designer','Design user interfaces and user experiences','Figma, Adobe XD, Design principles',NULL,'Pune','4-7 LPA','active','2026-05-07 20:03:35','2026-05-07 20:03:35');
INSERT INTO `jobs` VALUES('50','1','Senior Data Engineer','yfogcouhqcpibqipchpq','Data Science','python','Pune','12-15 LPA','active','2026-05-08 02:09:00','2026-05-08 02:09:00');



DROP TABLE IF EXISTS `leaderboard`;


CREATE TABLE `leaderboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role` varchar(100) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `quiz_result_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `quiz_result_id` (`quiz_result_id`),
  KEY `idx_role` (`role`),
  KEY `idx_score` (`score`),
  CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leaderboard_ibfk_2` FOREIGN KEY (`quiz_result_id`) REFERENCES `quiz_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `mock_interviews`;


CREATE TABLE `mock_interviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `job_role` varchar(255) NOT NULL,
  `interview_type` varchar(50) NOT NULL,
  `questions` text DEFAULT NULL,
  `answers` text DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `mock_interviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `parsed_resume_data`;


CREATE TABLE `parsed_resume_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resume_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `raw_text` text NOT NULL,
  `work_experience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`work_experience`)),
  `skills` text DEFAULT NULL,
  `education` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`education`)),
  `projects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`projects`)),
  `parsed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_resume_id` (`resume_id`),
  CONSTRAINT `parsed_resume_data_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `parsed_resume_data_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `parsed_resume_data` VALUES('1','1','1','1  \nPrajwal Yadav \nprajwal.yadav23@vit.edu | +91 9356850041 \nLinkedIn | LeetCode \n \n \nSummary \nI’m curious and driven learner with a passion for building and solving real world problems I have \ntaken part in several National Level Hackathon where I have learnt to think fast, working teams \nand turns ideas into working solutions. Currently i am focused on improving Data Structures and \nAlgorithms Skills. \n \nEducation \nSt.Xavier’s High School, Nashik, India 2010 – 2020 \nSSC 	Percentage: 89.9 \nNutan Vidyamandir, Nashik, India 2020 – 2022 \nHSC 	Percentage: 84.5 \nVishwakarma Institute of Technology, Pune, India \nBachelor of Technology in Information Technology 	CGPA: 8.52 \n2023 – 2027 \n \nProjects \nInnovestia :  Startup–Investor Matchmaking Platform| MongoDB, React, ExpressJS \n– A smart platform that bridges startups and investors by aligning their visions, domains, and growth \ntrajectories. \n    VishwaCollab :  College TPO Website | ReactJS, ExpressJS , MongoDB \n– Developed a full-stack Training and Placement platform enabling efficient management of student \nprofiles, job and internship postings, application tracking, and seamless communication between \nstudents, recruiters, and the placement cell. \n       |  \nTechnical Skills \nLanguages : 	C/C++, HTML/CSS, JS, ReactJS, ExpressJS,TypeScript \nDeveloper Tools : GitHub, MongoDB, Postman \n \n \nAchievements \n– 2nd runner-UP – NIRMAAN 3.0- Google Developer Groups, MIT Alandi. \n– Actively participate in Competetive Programming. \n– Participated in COEP Inspiron 4.0 Hackathon. \n– Partcipated in AISSMS InnovateYou Hackathon.','[]','[\"TypeScript\",\"C++\",\"Go\",\"React\",\"Express\",\"MongoDB\",\"Git\",\"HTML\",\"CSS\"]','[]','[]','2026-05-07 19:05:19','2026-05-07 19:05:19');



DROP TABLE IF EXISTS `quiz_questions`;


CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(100) NOT NULL,
  `question` text NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `correct_answer` int(11) NOT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




DROP TABLE IF EXISTS `quiz_results`;


CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role` varchar(100) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `correct_answers` int(11) NOT NULL,
  `time_taken` int(11) NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers`)),
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_role` (`role`),
  CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quiz_results` VALUES('1','1','student','80.00','5','4','0','[{\"question\":\"What hook is used to manage component state in functional components?\",\"skill\":\"React\",\"is_correct\":true,\"correct_option\":\"useState\",\"selected_option\":\"useState\",\"explanation\":\"useState returns a state variable and setter.\"},{\"question\":\"Which HTML5 element is semantic for navigation links?\",\"skill\":\"HTML\",\"is_correct\":true,\"correct_option\":\"<nav>\",\"selected_option\":\"<nav>\",\"explanation\":\"<nav> is used for groups of navigation links.\"},{\"question\":\"Which property controls the stacking order of positioned elements?\",\"skill\":\"CSS\",\"is_correct\":false,\"correct_option\":\"z-index\",\"selected_option\":\"position\",\"explanation\":\"z-index controls stacking order.\"},{\"question\":\"Which Git command creates a copy of a repository from a remote source?\",\"skill\":\"General\",\"is_correct\":true,\"correct_option\":\"git clone\",\"selected_option\":\"git clone\",\"explanation\":\"git clone creates a working copy of a repository.\"},{\"question\":\"Which HTTP status code indicates a successful request?\",\"skill\":\"General\",\"is_correct\":true,\"correct_option\":\"200\",\"selected_option\":\"200\",\"explanation\":\"200 OK indicates success.\"}]','2026-05-08 02:32:48','2026-05-08 02:32:48');



DROP TABLE IF EXISTS `resumes`;


CREATE TABLE `resumes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `resumes` VALUES('1','1','Resume_Prajwal_Yadav.pdf','uploads/resumes/69fc951798189_Resume_Prajwal_Yadav.pdf','366148','application/pdf','2026-05-07 19:05:19','2026-05-07 19:05:19');



DROP TABLE IF EXISTS `students`;


CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `experience` text DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `students` VALUES('1','1','Py08oct','py08oct@gmail.com','','TypeScript, C++, Go, React, Express, MongoDB, Git, HTML, CSS','8.52','',NULL,NULL,'2026-05-07 18:51:46');
INSERT INTO `students` VALUES('2','1','Py08oct','rahul@example.com','','TypeScript, C++, Go, React, Express, MongoDB, Git, HTML, CSS','8.52','',NULL,NULL,'2026-05-07 20:03:18');
INSERT INTO `students` VALUES('3','2','Priya Patel','priya@example.com','Information Technology','Data Science, Python, SQL, Statistics','9.50',NULL,NULL,NULL,'2026-05-07 20:03:18');
INSERT INTO `students` VALUES('4','3','Amit Kumar','amit@example.com','Computer Engineering','Android Development, Java, Kotlin, UI/UX','8.90',NULL,NULL,NULL,'2026-05-07 20:03:18');
INSERT INTO `students` VALUES('12','1','Py08oct','rahul@example.com','','TypeScript, C++, Go, React, Express, MongoDB, Git, HTML, CSS','8.52','',NULL,NULL,'2026-05-07 20:03:31');
INSERT INTO `students` VALUES('13','2','Priya Patel','priya@example.com','Information Technology','Data Science, Python, SQL, Statistics','9.50',NULL,NULL,NULL,'2026-05-07 20:03:31');
INSERT INTO `students` VALUES('14','3','Amit Kumar','amit@example.com','Computer Engineering','Android Development, Java, Kotlin, UI/UX','8.90',NULL,NULL,NULL,'2026-05-07 20:03:31');
INSERT INTO `students` VALUES('22','1','Py08oct','rahul@example.com','','TypeScript, C++, Go, React, Express, MongoDB, Git, HTML, CSS','8.52','',NULL,NULL,'2026-05-07 20:03:35');
INSERT INTO `students` VALUES('23','2','Priya Patel','priya@example.com','Information Technology','Data Science, Python, SQL, Statistics','9.50',NULL,NULL,NULL,'2026-05-07 20:03:35');
INSERT INTO `students` VALUES('24','3','Amit Kumar','amit@example.com','Computer Engineering','Android Development, Java, Kotlin, UI/UX','8.90',NULL,NULL,NULL,'2026-05-07 20:03:35');



DROP TABLE IF EXISTS `users`;


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','company','tpo') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES('1','Py08oct','py08oct@gmail.com','$2y$12$s0FxjVtvrRxwTrUfHE8dju5lu8.fgf.pzEZwgsJnTOPStAXXrAMQm','student','2026-05-07 18:51:46');
INSERT INTO `users` VALUES('2','Company','company@gmail.com','$2y$12$M2K/6j.FKkedDOjEgLfsLe6nNI92ka2CBwR0SCEz5i94QaqZdyXIu','company','2026-05-07 18:53:02');
INSERT INTO `users` VALUES('3','Tpo','tpo@gmail.com','$2y$12$dOOP7bvWykv9c5p.VG0wzeLx0aU/zkQy8.ASdZziPjVe120TCW0SK','tpo','2026-05-07 18:56:27');
INSERT INTO `users` VALUES('101','TechCorp Solutions','hr@techcorp.com','$2y$10$8C0x6pGmY2C6iM6Q1q1zSuy0kY2o0k7eC2q1jB6s6B1FQ2hQf2k1K','company','2026-05-07 20:03:18');
INSERT INTO `users` VALUES('201','TPO Admin','tpo@vishwacollab.com','$2y$10$8C0x6pGmY2C6iM6Q1q1zSuy0kY2o0k7eC2q1jB6s6B1FQ2hQf2k1K','tpo','2026-05-07 20:03:18');



SET FOREIGN_KEY_CHECKS=1;
