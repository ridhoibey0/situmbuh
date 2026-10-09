-- Data referensi KPSP (kategori usia, kategori pertanyaan, pertanyaan). Dimuat oleh ReferenceDataSeeder.
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE kpsp_questions; TRUNCATE TABLE question_categories; TRUNCATE TABLE age_categories;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `age_categories` WRITE;
/*!40000 ALTER TABLE `age_categories` DISABLE KEYS */;
INSERT INTO `age_categories` (`id`, `name`, `min_age`, `max_age`, `created_at`, `updated_at`) VALUES (1,'0 - 3 Bulan',0,3,'2025-06-03 18:57:44','2025-06-03 19:32:25'),
(2,'0 - 3 Bulam',1,1,'2025-07-16 03:18:31','2025-07-16 03:18:31'),
(4,'36 bulan ke atas',36,216,'2026-06-24 15:37:15','2026-06-24 15:37:15');
/*!40000 ALTER TABLE `age_categories` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `question_categories` WRITE;
/*!40000 ALTER TABLE `question_categories` DISABLE KEYS */;
INSERT INTO `question_categories` (`id`, `name`, `created_at`, `updated_at`) VALUES (1,'KPSP','2025-06-06 08:45:58','2025-06-06 08:46:03'),
(2,'Deteksi Dini Penyimpangan Pendengaran Anak','2025-06-06 08:46:08','2025-06-06 08:46:08'),
(3,'Deteksi Pupil Putih','2025-06-09 17:34:45','2025-06-09 17:34:45'),
(4,'Test Daya Lihat','2025-06-09 19:02:16','2025-06-09 19:02:16'),
(5,'Deteksi Dini Penyimpangan Perilaku dan Emosi','2025-06-09 19:03:01','2025-06-09 19:03:01'),
(6,'Deteksi Dini Gangguan Spektrum Autisme pada Anak','2025-06-09 19:22:17','2025-06-09 19:22:17'),
(7,'Deteksi Dini GPPH','2026-06-24 15:37:15','2026-06-24 15:37:15');
/*!40000 ALTER TABLE `question_categories` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `kpsp_questions` WRITE;
/*!40000 ALTER TABLE `kpsp_questions` DISABLE KEYS */;
INSERT INTO `kpsp_questions` (`id`, `age_category_id`, `question`, `image`, `created_at`, `updated_at`, `description`, `category_id`) VALUES (1,1,'<p>Bayi diposisikan terlentang. Ambil gulungan wool merah, letakkan di atas wajah di depan mata bayi. Gerakkan wool dari samping kiri ke kanan kepala. Apakah ia dapat mengikuti gerakan Anda dengan menggerakkan kepala sepenuhnya dari satu ke sisi yang lain?</p>\n<p><img alt=\"\" src=\"https://situba.com/storage/assets/product/mfDH9CPlKAR43FXAN7NJqgpdKbVaBeC0UmlsE7He.jpg\" style=\"height:100px; width:258px\" />​​​​​​​</p>','assets/product/rdGiFtB2aoCtbfdUddXzChbCSJshi7a5lqRTXLHY.png','2025-06-05 17:52:28','2025-06-05 17:52:28','Gerak Kasar',1),
(2,1,'Jangan membuat suara apapun. Pada saat bayi terlentang apakah ia melihat dan menatap wajah Anda?',NULL,'2025-06-05 18:03:30','2025-06-05 18:03:30','Sosialisasi Dan Kemandirian',1),
(3,1,'Pada saat Anda mengajak bayi berbicara dan tersenyum, apakah ia tersenyum kembali kepada Anda?',NULL,'2025-06-05 18:03:48','2025-06-05 18:03:48','Sosialisasi Dan Kemandirian',1),
(4,1,'Apakah bayi dapat mengeluarkan suara-suara lain (mengoceh) selain menangis?',NULL,'2025-06-05 18:04:07','2025-06-05 18:04:07','Bicara dan Bahasa',1),
(5,1,'Apakah bayi suka tertawa keras walau tidak digelitik atau diraba-raba?',NULL,'2025-06-05 18:04:35','2025-06-05 18:04:35','Bicara dan Bahasa',1),
(6,1,'Ambil gulungan wool merah, lalu letakkan di atas wajah di depan mata bayi. Gerakkan wool dari\r\nsamping kiri ke kanan kepala atau sebaliknya. Apakah ia dapat mengikuti gerakan Anda dengan\r\nmenggerakkan kepalanya dari kanan atau kiri ke tengah?',NULL,'2025-06-05 18:05:22','2025-06-05 18:05:22','Gerak Halus',1),
(7,1,'Ambil gulungan wool merah, lalu letakkan di atas wajah di depan mata bayi. Gerakkan wool\r\ndari samping kiri ke kanan kepala atau sebaliknya. Apakah ia dapat mengikuti gerakan Anda\r\ndengan menggerakkan kepalanya dari satu sisi hampir sampai pada sisi yang lain?',NULL,'2025-06-05 18:06:15','2025-06-05 18:06:15','Gerak Halus',1),
(8,1,'Pada saat bayi tengkurap di alas yang datar, apakah ia dapat mengangkat kepalanya seperti\r\npada gambar?',NULL,'2025-06-05 18:06:34','2025-06-05 18:06:34','Gerak Kasar',1),
(9,1,'Pada saat bayi tengkurap di alas yang datar, apakah ia dapat mengangkat kepalanya sehingga\r\nmembentuk sudut 45˚ seperti pada gambar?',NULL,'2025-06-05 18:06:52','2025-06-05 18:06:52','Gerak Kasar',1),
(10,1,'Pada saat bayi tengkurap di alas yang datar, apakah ia dapat mengangkat kepalanya dengan tegak\r\nseperti pada gambar?',NULL,'2025-06-05 18:07:04','2025-06-05 18:07:04','Gerak Kasar',1),
(12,1,'Test',NULL,'2025-06-09 18:44:17','2025-06-09 18:44:17','tes',2),
(13,1,'Terdapat refleks\r\nmerah terang dan\r\nekual pada Tes Refleks\r\nMerah atau Bruckner\r\ntest',NULL,'2025-06-09 18:52:11','2025-06-09 18:52:11','Gerak Kasar',3),
(14,1,'Pupil tampak hitam\r\npada pemeriksaan\r\ndengan senter atau\r\nblitz kamera',NULL,'2025-06-09 18:52:29','2025-06-09 18:52:29','Gerak Halus',3),
(15,1,'Anak dapat\r\nmenjawab dengan\r\nbenar arah kaki “E” 3\r\nkali berturut- turut,\r\nATAU anak\r\nmenjawab benar 4\r\natau lebih dari 5 kali\r\nkesempatan',NULL,'2025-06-09 19:03:27','2025-06-09 19:03:27','Gerak Kasar',4),
(16,1,'Test 1',NULL,'2025-06-09 19:08:27','2025-06-09 19:08:27',NULL,5),
(17,1,'Test 2',NULL,'2025-06-09 19:08:36','2025-06-09 19:08:36',NULL,5),
(18,1,'Test 3',NULL,'2025-06-09 19:08:48','2025-06-09 19:08:48',NULL,5),
(19,1,'Jika Anda menunjuk sesuatu di ruangan, apakah anak Anda melihatnya? (Misalnya, jika Anda menunjuk hewan atau\r\nmainan, apakah anak Anda melihat ke arah hewan atau mainan yang anda tunjuk?)',NULL,'2025-06-09 19:23:08','2025-06-09 19:23:08',NULL,6),
(20,1,'Pernahkah Anda berpikir bahwa anak Anda tuli?',NULL,'2025-06-09 19:23:23','2025-06-09 19:23:23',NULL,6),
(21,1,'Apakah anak Anda pernah bermain pura-pura? (Misalnya, berpura-pura minum dari gelas kosong, berpura-pura berbicara\r\nmenggunakan telepon, atau menyuapi boneka atau boneka binatang?)',NULL,'2025-06-09 19:23:37','2025-06-09 19:23:37',NULL,6),
(22,1,'Apakah anak Anda suka memanjat benda-benda? (Misalnya, furnitur, alat-alat bermain, atau tangga)',NULL,'2025-06-09 19:23:53','2025-06-09 19:23:53',NULL,6),
(23,1,'<p>Tanpa bimbingan, petunjuk, atau bantuan Anda, dapatkah anak menyebut 2 gambar di antara gambar-gambar di</p>\r\n\r\n<p><img alt=\"\" src=\"https://situba.com/storage/assets/product/mfDH9CPlKAR43FXAN7NJqgpdKbVaBeC0UmlsE7He.jpg\" style=\"height:39px; width:100px\" /></p>\r\n\r\n<p>Jawab &lsquo;Ya&rsquo; bila ia menggambar garis seperti ini:<img alt=\"\" src=\"https://situba.com/storage/assets/product/mfDH9CPlKAR43FXAN7NJqgpdKbVaBeC0UmlsE7He.jpg\" style=\"height:39px; width:100px\" />​​​​​​​</p>\r\n\r\n<p>Jawab &lsquo;Ya&rsquo; bila ia menggambar garis seperti ini:</p>',NULL,'2025-07-16 13:12:55','2025-07-16 13:12:55',NULL,1),
(24,1,'<p>Tanpa bimbingan, petunjuk, atau bantuan Anda, dapatkah anak menyebut 2 gambar di antara gambar-gambar di</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Jawab &lsquo;Ya&rsquo; bila ia menggambar garis seperti ini:<img alt=\"\" src=\"https://situba.com/storage/assets/product/mfDH9CPlKAR43FXAN7NJqgpdKbVaBeC0UmlsE7He.jpg\" style=\"height:78px; width:200px\" />​​​​​​​</p>\r\n\r\n<p>Jawab &lsquo;Tidak&rsquo; bila ia menggambar garis seperti ini:<img alt=\"\" src=\"https://situba.com/storage/assets/product/mfDH9CPlKAR43FXAN7NJqgpdKbVaBeC0UmlsE7He.jpg\" style=\"height:78px; width:200px\" />​​​​​​​</p>',NULL,'2025-07-16 13:15:01','2025-07-16 13:15:01',NULL,1),
(25,4,'Tidak kenal lelah atau aktivitas yang berlebihan.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(26,4,'Mudah menjadi gembira, impulsif.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(27,4,'Mengganggu anak-anak lain.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(28,4,'Gagal menyelesaikan kegiatan yang telah dimulai, rentang perhatian pendek.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(29,4,'Menggerak-gerakkan anggota badan atau kepala secara terus-menerus.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(30,4,'Kurang perhatian, mudah teralihkan.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(31,4,'Permintaannya harus segera dipenuhi, mudah menjadi frustasi.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(32,4,'Sering dan mudah menangis.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(33,4,'Suasana hatinya (mood) mudah berubah dengan cepat dan drastis.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7),
(34,4,'Ledakan suasana hati (mood) yang bersifat eksplosif dan tak terduga.','','2026-06-24 15:37:15','2026-06-24 15:37:15',NULL,7);
/*!40000 ALTER TABLE `kpsp_questions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS=1;
