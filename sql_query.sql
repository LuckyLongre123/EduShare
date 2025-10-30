-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2025 at 06:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39937351_project_db`
--

-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS if0_39937351_project_db;
USE if0_39937351_project_db;

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `college_id` int(11) NOT NULL,
  `college_name` varchar(200) NOT NULL,
  `university` varchar(200) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment_text` text NOT NULL,
  `comment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `like_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `material_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `college_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `downloads` int(11) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `created_at`) VALUES
(1, 'Mathematics', '2025-09-12 00:45:21'),
(2, 'Physics', '2025-09-12 00:45:21'),
(3, 'Chemistry', '2025-09-12 00:45:21'),
(4, 'Biology', '2025-09-12 00:45:21'),
(5, 'Computer Science', '2025-09-12 00:45:21'),
(6, 'English', '2025-09-12 00:45:21'),
(7, 'History', '2025-09-12 00:45:21'),
(8, 'Geography', '2025-09-12 00:45:21'),
(9, 'Political Science', '2025-09-12 00:45:21'),
(10, 'Economics', '2025-09-12 00:45:21'),
(11, 'Sociology', '2025-09-12 00:45:21'),
(12, 'Psychology', '2025-09-12 00:45:21'),
(13, 'Philosophy', '2025-09-12 00:45:21'),
(14, 'Statistics', '2025-09-12 00:45:21'),
(15, 'Environmental Science', '2025-09-12 00:45:21'),
(16, 'Commerce', '2025-09-12 00:45:21'),
(17, 'Business Studies', '2025-09-12 00:45:21'),
(18, 'Accounting', '2025-09-12 00:45:21'),
(19, 'Law', '2025-09-12 00:45:21'),
(20, 'Algebra', '2025-09-12 00:45:21'),
(21, 'Geometry', '2025-09-12 00:45:21'),
(22, 'Trigonometry', '2025-09-12 00:45:21'),
(23, 'Calculus', '2025-09-12 00:45:21'),
(24, 'Probability', '2025-09-12 00:45:21'),
(25, 'Linear Algebra', '2025-09-12 00:45:21'),
(26, 'Differential Equations', '2025-09-12 00:45:21'),
(27, 'Number Theory', '2025-09-12 00:45:21'),
(28, 'Programming', '2025-09-12 00:45:21'),
(29, 'Data Structures', '2025-09-12 00:45:21'),
(30, 'Algorithms', '2025-09-12 00:45:21'),
(31, 'Database', '2025-09-12 00:45:21'),
(32, 'Operating Systems', '2025-09-12 00:45:21'),
(33, 'Computer Networks', '2025-09-12 00:45:21'),
(34, 'Web Development', '2025-09-12 00:45:21'),
(35, 'Software Engineering', '2025-09-12 00:45:21'),
(36, 'Artificial Intelligence', '2025-09-12 00:45:21'),
(37, 'Machine Learning', '2025-09-12 00:45:21'),
(38, 'Cybersecurity', '2025-09-12 00:45:21'),
(39, 'Organic Chemistry', '2025-09-12 00:45:21'),
(40, 'Inorganic Chemistry', '2025-09-12 00:45:21'),
(41, 'Physical Chemistry', '2025-09-12 00:45:21'),
(42, 'Zoology', '2025-09-12 00:45:21'),
(43, 'Botany', '2025-09-12 00:45:21'),
(44, 'Microbiology', '2025-09-12 00:45:21'),
(45, 'Biotechnology', '2025-09-12 00:45:21'),
(46, 'Mechanics', '2025-09-12 00:45:21'),
(47, 'Thermodynamics', '2025-09-12 00:45:21'),
(48, 'Electricity', '2025-09-12 00:45:21'),
(49, 'Magnetism', '2025-09-12 00:45:21'),
(50, 'Quantum Physics', '2025-09-12 00:45:21'),
(51, 'Lecture Notes', '2025-09-12 00:45:21'),
(52, 'Assignments', '2025-09-12 00:45:21'),
(53, 'Question Papers', '2025-09-12 00:45:21'),
(54, 'Solutions', '2025-09-12 00:45:21'),
(55, 'Lab Manual', '2025-09-12 00:45:21'),
(56, 'Experiments', '2025-09-12 00:45:21'),
(57, 'Diagrams', '2025-09-12 00:45:21'),
(58, 'Case Studies', '2025-09-12 00:45:21'),
(59, 'Research Papers', '2025-09-12 00:45:21'),
(60, 'Class 9', '2025-09-12 00:45:21'),
(61, 'Class 10', '2025-09-12 00:45:21'),
(62, 'Class 11', '2025-09-12 00:45:21'),
(63, 'Class 12', '2025-09-12 00:45:21'),
(64, 'Undergraduate', '2025-09-12 00:45:21'),
(65, 'Postgraduate', '2025-09-12 00:45:21'),
(66, 'Diploma', '2025-09-12 00:45:21'),
(67, 'Vocational', '2025-09-12 00:45:21'),
(68, 'PDF', '2025-09-12 00:45:21'),
(69, 'DOC', '2025-09-12 00:45:21'),
(70, 'PPT', '2025-09-12 00:45:21'),
(71, 'Text Notes', '2025-09-12 00:45:21'),
(72, 'Handwritten Notes', '2025-09-12 00:45:21'),
(73, 'E-Book', '2025-09-12 00:45:21'),
(74, 'Study Guide', '2025-09-12 00:45:21'),
(75, 'Revision Notes', '2025-09-12 00:45:21'),
(76, 'Sample Papers', '2025-09-12 00:45:21'),
(77, 'Previous Year Papers', '2025-09-12 00:45:21'),
(78, 'C', '2025-09-12 00:45:21'),
(79, 'C++', '2025-09-12 00:45:21'),
(80, 'Python', '2025-09-12 00:45:21'),
(81, 'Java', '2025-09-12 00:45:21'),
(82, 'JavaScript', '2025-09-12 00:45:21'),
(83, 'TypeScript', '2025-09-12 00:45:21'),
(84, 'PHP', '2025-09-12 00:45:21'),
(85, 'HTML', '2025-09-12 00:45:21'),
(86, 'CSS', '2025-09-12 00:45:21'),
(87, 'SQL', '2025-09-12 00:45:21'),
(88, 'Frontend', '2025-09-12 00:45:21'),
(89, 'Backend', '2025-09-12 00:45:21'),
(90, 'Full Stack', '2025-09-12 00:45:21'),
(91, 'React', '2025-09-12 00:45:21'),
(92, 'Angular', '2025-09-12 00:45:21'),
(93, 'Node.js', '2025-09-12 00:45:21'),
(94, 'Django', '2025-09-12 00:45:21'),
(95, 'Flask', '2025-09-12 00:45:21'),
(96, 'Competitive Programming', '2025-09-12 00:45:21'),
(97, 'Problem Solving', '2025-09-12 00:45:21'),
(98, 'Dynamic Programming', '2025-09-12 00:45:21'),
(99, 'Graph Theory', '2025-09-12 00:45:21'),
(100, 'Deep Learning', '2025-09-12 00:45:21'),
(101, 'Neural Networks', '2025-09-12 00:45:21'),
(102, 'Data Science', '2025-09-12 00:45:21'),
(103, 'Data Analysis', '2025-09-12 00:45:21'),
(104, 'Ethical Hacking', '2025-09-12 00:45:21'),
(105, 'Cryptography', '2025-09-12 00:45:21'),
(106, 'Cloud Computing', '2025-09-12 00:45:21'),
(107, 'AWS', '2025-09-12 00:45:21'),
(108, 'Docker', '2025-09-12 00:45:21'),
(109, 'Kubernetes', '2025-09-12 00:45:21'),
(110, 'Git', '2025-09-12 00:45:21'),
(111, 'GitHub', '2025-09-12 00:45:21'),
(112, 'Projects', '2025-09-12 00:45:21'),
(113, 'Internships', '2025-09-12 00:45:21'),
(114, 'Seminars', '2025-09-12 00:45:21'),
(115, 'Workshops', '2025-09-12 00:45:21'),
(116, 'Presentations', '2025-09-12 00:45:21'),
(117, 'Research', '2025-09-12 00:45:21'),
(118, 'Career Guidance', '2025-09-12 00:45:21'),
(119, 'Soft Skills', '2025-09-12 00:45:21'),
(120, 'Time Management', '2025-09-12 00:45:21'),
(121, 'Leadership', '2025-09-12 00:45:21'),
(122, 'Communication Skills', '2025-09-12 00:45:21'),
(123, 'Interview Preparation', '2025-09-12 00:45:21'),
(124, 'Resume Building', '2025-09-12 00:45:21'),
(125, 'Mock Tests', '2025-09-12 00:45:21'),
(126, 'Study Tips', '2025-09-12 00:45:21'),
(127, 'Online Courses', '2025-09-12 00:45:21'),
(128, 'Blockchain', '2025-09-12 00:45:21'),
(129, 'IoT', '2025-09-12 00:45:21'),
(130, 'Augmented Reality', '2025-09-12 00:45:21'),
(131, 'Virtual Reality', '2025-09-12 00:45:21'),
(132, 'Robotics', '2025-09-12 00:45:21'),
(133, 'Embedded Systems', '2025-09-12 00:45:21'),
(134, 'Compiler Design', '2025-09-12 00:45:21'),
(135, 'Operating System Concepts', '2025-09-12 00:45:21'),
(136, 'Software Testing', '2025-09-12 00:45:21'),
(137, 'Data Mining', '2025-09-12 00:45:21'),
(138, 'Big Data', '2025-09-12 00:45:21'),
(139, 'Hadoop', '2025-09-12 00:45:21'),
(140, 'Spark', '2025-09-12 00:45:21'),
(141, 'NoSQL', '2025-09-12 00:45:21'),
(142, 'MongoDB', '2025-09-12 00:45:21'),
(143, 'PostgreSQL', '2025-09-12 00:45:21'),
(144, 'SQLite', '2025-09-12 00:45:21'),
(145, 'JavaFX', '2025-09-12 00:45:21'),
(146, 'Swing', '2025-09-12 00:45:21'),
(147, 'Kotlin', '2025-09-12 00:45:21'),
(148, 'Swift', '2025-09-12 00:45:21'),
(149, 'Objective-C', '2025-09-12 00:45:21'),
(150, 'Mobile Development', '2025-09-12 00:45:21'),
(151, 'Android', '2025-09-12 00:45:21'),
(152, 'iOS', '2025-09-12 00:45:21'),
(153, 'Flutter', '2025-09-12 00:45:21'),
(154, 'React Native', '2025-09-12 00:45:21'),
(155, 'Machine Vision', '2025-09-12 00:45:21'),
(156, 'Natural Language Processing', '2025-09-12 00:45:21'),
(157, 'Computer Vision', '2025-09-12 00:45:21'),
(158, 'Reinforcement Learning', '2025-09-12 00:45:21'),
(159, 'TensorFlow', '2025-09-12 00:45:21'),
(160, 'PyTorch', '2025-09-12 00:45:21'),
(161, 'Scikit-learn', '2025-09-12 00:45:21'),
(162, 'Pandas', '2025-09-12 00:45:21'),
(163, 'NumPy', '2025-09-12 00:45:21'),
(164, 'Matplotlib', '2025-09-12 00:45:21'),
(165, 'Seaborn', '2025-09-12 00:45:21'),
(166, 'Excel', '2025-09-12 00:45:21'),
(167, 'Power BI', '2025-09-12 00:45:21'),
(168, 'Tableau', '2025-09-12 00:45:21'),
(169, 'Google Analytics', '2025-09-12 00:45:21'),
(170, 'SEO', '2025-09-12 00:45:21'),
(171, 'Digital Marketing', '2025-09-12 00:45:21'),
(172, 'Social Media Marketing', '2025-09-12 00:45:21'),
(173, 'Content Writing', '2025-09-12 00:45:21'),
(174, 'Technical Writing', '2025-09-12 00:45:21'),
(175, 'Public Speaking', '2025-09-12 00:45:21'),
(176, 'Negotiation Skills', '2025-09-12 00:45:21'),
(177, 'Critical Thinking', '2025-09-12 00:45:21'),
(178, 'Problem Solving Techniques', '2025-09-12 00:45:21'),
(179, 'Logical Reasoning', '2025-09-12 00:45:21'),
(180, 'Quantitative Aptitude', '2025-09-12 00:45:21'),
(181, 'Verbal Ability', '2025-09-12 00:45:21'),
(182, 'Group Discussion', '2025-09-12 00:45:21'),
(183, 'Aptitude', '2025-09-12 00:45:21'),
(184, 'Mock Interviews', '2025-09-12 00:45:21'),
(185, 'Competitive Exams', '2025-09-12 00:45:21'),
(186, 'SSC', '2025-09-12 00:45:21'),
(187, 'UPSC', '2025-09-12 00:45:21'),
(188, 'Banking Exams', '2025-09-12 00:45:21'),
(189, 'GATE', '2025-09-12 00:45:21'),
(190, 'CAT', '2025-09-12 00:45:21'),
(191, 'JEE', '2025-09-12 00:45:21'),
(192, 'NEET', '2025-09-12 00:45:21'),
(193, 'Olympiads', '2025-09-12 00:45:21'),
(194, 'Physics Olympiad', '2025-09-12 00:45:21'),
(195, 'Math Olympiad', '2025-09-12 00:45:21'),
(196, 'Chemistry Olympiad', '2025-09-12 00:45:21'),
(197, 'Biology Olympiad', '2025-09-12 00:45:21'),
(198, 'Essay Writing', '2025-09-12 00:45:21'),
(199, 'Case Analysis', '2025-09-12 00:45:21'),
(200, 'Presentation Skills', '2025-09-12 00:45:21'),
(201, 'French', '2025-09-12 00:45:21'),
(202, 'Spanish', '2025-09-12 00:45:21'),
(203, 'German', '2025-09-12 00:45:21'),
(204, 'Japanese', '2025-09-12 00:45:21'),
(205, 'Chinese', '2025-09-12 00:45:21'),
(206, 'Hindi', '2025-09-12 00:45:21'),
(207, 'Marathi', '2025-09-12 00:45:21'),
(208, 'Sanskrit', '2025-09-12 00:45:21'),
(209, 'Creative Writing', '2025-09-12 00:45:21'),
(210, 'Poetry', '2025-09-12 00:45:21'),
(211, 'Story Writing', '2025-09-12 00:45:21'),
(212, 'Grammar', '2025-09-12 00:45:21'),
(213, 'Vocabulary', '2025-09-12 00:45:21'),
(214, 'Essay', '2025-09-12 00:45:21'),
(215, 'Comprehension', '2025-09-12 00:45:21'),
(217, 'Resume Writing', '2025-09-12 00:45:21'),
(218, 'Cover Letter', '2025-09-12 00:45:21'),
(221, 'Teamwork', '2025-09-12 00:45:21'),
(222, 'Project Management', '2025-09-12 00:45:21'),
(233, 'Vue.js', '2025-09-12 00:45:21'),
(235, 'Laravel', '2025-09-12 00:45:21'),
(242, 'Firebase', '2025-09-12 00:45:21'),
(244, 'Azure', '2025-09-12 00:45:21'),
(245, 'Google Cloud', '2025-09-12 00:45:21'),
(254, 'Data Analytics', '2025-09-12 00:45:21'),
(255, 'Data Visualization', '2025-09-12 00:45:21'),
(259, 'R Programming', '2025-09-12 00:45:21'),
(260, 'SAS', '2025-09-12 00:45:21'),
(262, 'DevOps', '2025-09-12 00:45:21'),
(267, 'GitLab', '2025-09-12 00:45:21'),
(268, 'CI/CD', '2025-09-12 00:45:21'),
(269, 'Agile', '2025-09-12 00:45:21'),
(270, 'Scrum', '2025-09-12 00:45:21'),
(271, 'Kanban', '2025-09-12 00:45:21'),
(272, 'Project Scheduling', '2025-09-12 00:45:21'),
(273, 'Risk Management', '2025-09-12 00:45:21'),
(274, 'Supply Chain Management', '2025-09-12 00:45:21'),
(275, 'Marketing', '2025-09-12 00:45:21'),
(279, 'Social Media', '2025-09-12 00:45:21'),
(280, 'Brand Management', '2025-09-12 00:45:21'),
(281, 'Finance', '2025-09-12 00:45:21'),
(282, 'Investment', '2025-09-12 00:45:21'),
(283, 'Stock Market', '2025-09-12 00:45:21'),
(284, 'Mutual Funds', '2025-09-12 00:45:21'),
(285, 'Insurance', '2025-09-12 00:45:21'),
(286, 'Entrepreneurship', '2025-09-12 00:45:21'),
(287, 'Startups', '2025-09-12 00:45:21'),
(288, 'Business Analytics', '2025-09-12 00:45:21'),
(289, 'Econometrics', '2025-09-12 00:45:21'),
(290, 'Statistics Software', '2025-09-12 00:45:21'),
(291, 'SPSS', '2025-09-12 00:45:21'),
(292, 'MATLAB', '2025-09-12 00:45:21'),
(293, 'Simulation', '2025-09-12 00:45:21'),
(296, 'Electronics', '2025-09-12 00:45:21'),
(297, 'Circuit Design', '2025-09-12 00:45:21'),
(299, 'Arduino', '2025-09-12 00:45:21'),
(300, 'Raspberry Pi', '2025-09-12 00:45:21'),
(301, '3D Printing', '2025-09-12 00:45:21'),
(302, 'Mechanical Engineering', '2025-09-12 00:45:21'),
(303, 'Civil Engineering', '2025-09-12 00:45:21'),
(304, 'Electrical Engineering', '2025-09-12 00:45:21'),
(305, 'Chemical Engineering', '2025-09-12 00:45:21'),
(306, 'Architecture', '2025-09-12 00:45:21'),
(307, 'Design Thinking', '2025-09-12 00:45:21'),
(308, 'Animation', '2025-09-12 00:45:21'),
(309, 'Graphic Design', '2025-09-12 00:45:21'),
(310, 'UI/UX', '2025-09-12 00:45:21'),
(311, 'Photography', '2025-09-12 00:45:21'),
(312, 'Video Editing', '2025-09-12 00:45:21'),
(313, 'Music', '2025-09-12 00:45:21'),
(314, 'Dance', '2025-09-12 00:45:21'),
(315, 'Painting', '2025-09-12 00:45:21'),
(316, 'Calligraphy', '2025-09-12 00:45:21'),
(317, 'Drama', '2025-09-12 00:45:21'),
(319, 'Debate', '2025-09-12 00:45:21'),
(320, 'Quiz', '2025-09-12 00:45:21'),
(321, 'Model United Nations', '2025-09-12 00:45:21'),
(322, 'Volunteering', '2025-09-12 00:45:21'),
(323, 'Social Work', '2025-09-12 00:45:21'),
(324, 'Personality Development', '2025-09-12 00:45:21'),
(327, 'Research Methodology', '2025-09-12 00:45:21'),
(328, 'Academic Writing', '2025-09-12 00:45:21'),
(330, 'Negotiation', '2025-09-12 00:45:21'),
(331, 'Conflict Resolution', '2025-09-12 00:45:21'),
(332, 'Time Management Techniques', '2025-09-12 00:45:21'),
(333, 'Leadership Skills', '2025-09-12 00:45:21'),
(334, 'Emotional Intelligence', '2025-09-12 00:45:21'),
(335, 'Mindfulness', '2025-09-12 00:45:21'),
(336, 'Yoga', '2025-09-12 00:45:21'),
(337, 'Fitness', '2025-09-12 00:45:21'),
(338, 'Nutrition', '2025-09-12 00:45:21'),
(339, 'Health Education', '2025-09-12 00:45:21'),
(341, 'Job Skills', '2025-09-12 00:45:21'),
(342, 'Interview Tips', '2025-09-12 00:45:21'),
(343, 'Freelancing', '2025-09-12 00:45:21'),
(344, 'Portfolio Building', '2025-09-12 00:45:21'),
(346, 'Entrepreneur Skills', '2025-09-12 00:45:21'),
(349, 'Certifications', '2025-09-12 00:45:21'),
(352, 'Astronomy', '2025-10-30 04:30:00'),
(356, 'Quantum Computing', '2025-10-30 04:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `college` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `joined_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `college`, `department`, `status`, `joined_on`) VALUES
(1, 'Lucky Longre', 'longerlucky588@gmail.com', '$2y$10$A0q8nL6QcU0Y99AyaSAO3.D5NCqeSe7wUxrM1Dc6mGOdbj6jsvUTy', 'admin', 'Ramanujan College', 'department of vocation', 'active', '2025-10-30 05:12:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`college_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`material_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `college_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=358;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`material_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`college_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
