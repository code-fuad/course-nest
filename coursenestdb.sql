-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 08:16 PM
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
-- Database: `coursenestdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `bank_id` int(11) NOT NULL,
  `bank_password` varchar(20) NOT NULL,
  `student_id` int(15) NOT NULL,
  `account_balance` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(4) NOT NULL,
  `admin_password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_password`) VALUES
(3114, 'password');

-- --------------------------------------------------------

--
-- Table structure for table `advising_status`
--

CREATE TABLE `advising_status` (
  `status` varchar(3) NOT NULL DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `advising_status`
--

INSERT INTO `advising_status` (`status`) VALUES
('yes');

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `Application_Id` int(11) NOT NULL,
  `First_name` varchar(50) DEFAULT NULL,
  `Last_name` varchar(50) DEFAULT NULL,
  `Student_contact` bigint(20) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Blood_group` varchar(5) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `Father_name` varchar(100) DEFAULT NULL,
  `Mother_name` varchar(100) DEFAULT NULL,
  `Local_guardian` varchar(100) DEFAULT NULL,
  `Guardian_contact` bigint(20) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `SSC_year` year(4) DEFAULT NULL,
  `SSC_gpa` decimal(3,2) DEFAULT NULL,
  `SSC_roll` bigint(20) DEFAULT NULL,
  `SSC_reg` bigint(20) DEFAULT NULL,
  `SSC_in` varchar(100) DEFAULT NULL,
  `HSC_year` year(4) DEFAULT NULL,
  `HSC_gpa` decimal(3,2) DEFAULT NULL,
  `HSC_roll` bigint(20) DEFAULT NULL,
  `HSC_reg` bigint(20) DEFAULT NULL,
  `HSC_in` varchar(100) DEFAULT NULL,
  `Student_picture` varchar(255) DEFAULT NULL,
  `Student_signature` varchar(255) DEFAULT NULL,
  `SSC_certificate` varchar(255) DEFAULT NULL,
  `SSC_transcript` varchar(255) DEFAULT NULL,
  `HSC_certificate` varchar(255) DEFAULT NULL,
  `HSC_transcript` varchar(255) DEFAULT NULL,
  `approval_status` varchar(20) DEFAULT 'Pending',
  `dept_name` varchar(255) DEFAULT NULL,
  `major_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`Application_Id`, `First_name`, `Last_name`, `Student_contact`, `Gender`, `Blood_group`, `DOB`, `Father_name`, `Mother_name`, `Local_guardian`, `Guardian_contact`, `Address`, `SSC_year`, `SSC_gpa`, `SSC_roll`, `SSC_reg`, `SSC_in`, `HSC_year`, `HSC_gpa`, `HSC_roll`, `HSC_reg`, `HSC_in`, `Student_picture`, `Student_signature`, `SSC_certificate`, `SSC_transcript`, `HSC_certificate`, `HSC_transcript`, `approval_status`, `dept_name`, `major_name`) VALUES
(1, 'Tawsif ', 'Ahmed ', 1609503461, 'Male', 'B+', '2003-11-29', 'Jamal Hossain ', 'Kamrun Nahar', 'Shantunu Barua ', 1601280402, 'House 330-31,Afroza Begum Road, Bashundhara R/A', '2020', 5.00, 102993, 1714405398, 'Chittagong Government Hogh School ', '2022', 5.00, 114179, 1714405398, 'Bakalia Governmnet College ', '../uploads/1756798786_IMG_9726.jpg', '../uploads/1756798786_tawsif.jpg', '../uploads/1756798786_rsz_img_9726.jpg', '../uploads/1756798786_T-table.png', '../uploads/1756798786_Slide28.JPG', '../uploads/1756798786_ChatGPT Image Jul 14, 2025, 02_12_22 AM.png', 'Approved', 'ECE', 'Database Systems'),
(2, 'Sazid ', 'sdfssdfsd234', 123, 'Male', 'B+', '2025-08-06', 'asfas', 'sdasd', 'asdasd21312', 232323, 'dsfsdfsdfsd', '0000', 5.00, 2121, 112, 'fytfhgf', '0000', 4.00, 7764464, 446, 'uguhgjg', '../uploads/1756812706_rsz_img_9726.jpg', '../uploads/1756812706_T-table.png', '../uploads/1756812706_Logisim LAB 6.png', '../uploads/1756812706_TusharBhai.jpg', '../uploads/1756812706_cc04107e-f004-42ef-b62e-9b28b6b649cd.jpg', '../uploads/1756812706_tawsif.jpg', 'Approved', 'BMD', 'Database Systems'),
(3, 'jomila', 'khandakar', 234234234, 'Female', 'A+', '2025-09-16', '435345', '345345', 'ertgert', 435345345, 'dfgdfgdfgdfsgsdg', '0000', 9.99, 43534534, 345345345345, 'fgvdfgdfg', '0000', 9.99, 34534534, 345345345, 'fdsgdfsgsdfg', '../uploads/1756882782_1754012435_Screenshot 2025-07-14 010828.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 020615.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 010326.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 014655.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 005934.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 010326.png', 'Approved', 'BMD', 'Database Systems'),
(4, 'Sifat', 'Ahmed', 34234234, 'Male', 'AB+', '2025-09-09', 'Amanullah Shah', 'sadfasdfsdf', 'asdfdsaf', 5467456456, 'House- 60 , Road-5/A, Block- C, Arambag R/A, Mirpur, Dhaka-1216', '0000', 5.00, 123123, 45645645, 'asdaD', '0000', 9.99, 456456, 456456456456, 'dsafaasdf', '../uploads/1757088106_1754015717_Screenshot 2025-07-14 010326.png', '../uploads/1757088106_1754015717_Screenshot 2025-07-14 011333.png', '../uploads/1757088106_1754012435_Screenshot 2025-07-14 010828.png', '../uploads/1757088106_1756452834_1754015717_Screenshot 2025-07-14 010326.png', '../uploads/1757088106_1754015717_Screenshot 2025-07-14 010828.png', '../uploads/1757088106_1754015717_Screenshot 2025-07-14 010326.png', 'Approved', 'BMD', 'Database Systems');

--
-- Triggers `application`
--
DELIMITER $$
CREATE TRIGGER `data_fatcher_on_approval` AFTER UPDATE ON `application` FOR EACH ROW BEGIN
  IF NEW.approval_status = 'Approved' AND OLD.approval_status <> 'Approved' THEN
    INSERT INTO student (
      First_name, Last_name, Student_contact, Gender, Blood_group, DOB,
      Father_name, Mother_name, Local_guardian, Guardian_contact, Address,
      SSC_year, SSC_gpa, SSC_roll, SSC_reg, SSC_in,
      HSC_year, HSC_gpa, HSC_roll, HSC_reg, HSC_in,
      Dept_ID,
      Student_picture, Student_signature,
      SSC_certificate, SSC_transcript,
      HSC_certificate, HSC_transcript
    )
    VALUES (
      NEW.First_name, NEW.Last_name, NEW.Student_contact, NEW.Gender, NEW.Blood_group, NEW.DOB,
      NEW.Father_name, NEW.Mother_name, NEW.Local_guardian, NEW.Guardian_contact, NEW.Address,
      NEW.SSC_year, NEW.SSC_gpa, NEW.SSC_roll, NEW.SSC_reg, NEW.SSC_in,
      NEW.HSC_year, NEW.HSC_gpa, NEW.HSC_roll, NEW.HSC_reg, NEW.HSC_in,
      NULL,
      NEW.Student_picture, NEW.Student_signature,
      NEW.SSC_certificate, NEW.SSC_transcript,
      NEW.HSC_certificate, NEW.HSC_transcript
    );
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `chairman`
--

CREATE TABLE `chairman` (
  `chair_id` int(5) NOT NULL,
  `chair_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `contact` bigint(20) NOT NULL,
  `salary` decimal(7,2) NOT NULL,
  `department_id` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chairman`
--

INSERT INTO `chairman` (`chair_id`, `chair_name`, `password`, `gender`, `contact`, `salary`, `department_id`) VALUES
(25201, 'dfdxfcxd', 'Tawsif0786', 'Male', 54544545, 99999.99, 102),
(25202, 'Momin Molla', 'nsu123', 'Male', 1815071943, 20000.00, 103),
(25203, 'Shahidul Islam', 'lawchair123', 'Male', 1712345678, 90000.00, 101),
(25204, 'Farhana Kabir', 'ceechair123', 'Female', 1712345688, 92000.00, 104);

-- --------------------------------------------------------

--
-- Table structure for table `completed_course`
--

CREATE TABLE `completed_course` (
  `studentID` int(11) NOT NULL,
  `course_id` varchar(6) NOT NULL,
  `grade` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `completed_course`
--

INSERT INTO `completed_course` (`studentID`, `course_id`, `grade`) VALUES
(1, 'CEE310', 'A'),
(1, 'CEE370', 'A'),
(1, 'LAW101', 'A-'),
(1, 'LAW200', 'A'),
(2, 'CEE330', 'B+'),
(2, 'CEE335', 'A'),
(2, 'CEE371', 'A'),
(2, 'CEE475', 'A-');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` varchar(6) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `credit_point` int(11) NOT NULL,
  `credit_hour` int(11) NOT NULL,
  `department_id` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `course_name`, `credit_point`, `credit_hour`, `department_id`) VALUES
('CEE310', 'Quantity Survey and Cost Analysis', 3, 3, 104),
('CEE330', 'Structural Analysis and Design I', 3, 3, 104),
('CEE331', 'Structural Analysis and Design II', 3, 3, 104),
('CEE335', 'Reinforced Concrete Design I', 3, 3, 104),
('CEE340', 'Advanced Foundation Engineering', 3, 3, 104),
('CEE350', 'Traffic Analysis and Design', 3, 3, 104),
('CEE360', 'Open-Channel Hydraulics Lab', 1, 1, 104),
('CEE370', 'Water Supply and Treatment', 3, 3, 104),
('CEE371', 'Environmental Engineering Lab II', 1, 1, 104),
('CEE373', 'Sanitation and Wastewater Engineering Lab', 1, 1, 104),
('CEE415', 'Socio-economic Aspects of Development Projects', 3, 3, 104),
('CEE430', 'Reinforced Concrete Design II', 3, 3, 104),
('CEE431', 'Introduction to Structural Dynamics', 3, 3, 104),
('CEE435', 'Prestressed Concrete', 3, 3, 104),
('CEE460', 'Groundwater Hydraulics', 3, 3, 104),
('CEE470', 'Solid & Hazardous Waste Management', 3, 3, 104),
('CEE475', 'Water Resources and Environmental Modeling', 3, 3, 104),
('LAW101', 'Introduction to the Legal System & Legal Processes', 3, 3, 101),
('LAW200', 'Legal Environment of Business', 3, 3, 101);

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(3) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `location` varchar(50) DEFAULT NULL,
  `contact` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`, `location`, `contact`) VALUES
(101, 'LAW', 'Building A', 1234567890),
(102, 'BMD', 'Building B', 1234567891),
(103, 'ECE', 'Building C', 1234567892),
(104, 'CEE', 'Building D', 1234567893);

-- --------------------------------------------------------

--
-- Table structure for table `dept_program`
--

CREATE TABLE `dept_program` (
  `department_id` int(3) NOT NULL,
  `programs` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dept_program`
--

INSERT INTO `dept_program` (`department_id`, `programs`) VALUES
(101, 'LLB'),
(101, 'LLM'),
(102, 'Biomedical Engineering'),
(102, 'Medical Physics'),
(103, 'Computer Science Engineering'),
(103, 'Electrical & Computer Engg'),
(103, 'Electrical Engineering'),
(104, 'Civil Engineering'),
(104, 'Environmental Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `Teacher_ID` int(11) NOT NULL,
  `Course_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `faq_id` int(11) NOT NULL,
  `question` varchar(200) NOT NULL,
  `answer` varchar(200) NOT NULL,
  `student_id` int(4) NOT NULL,
  `admin_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pre_requisite`
--

CREATE TABLE `pre_requisite` (
  `course_id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `student_id` int(4) NOT NULL,
  `course_id` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`student_id`, `course_id`) VALUES
(1, 'CEE310'),
(4, 'CEE310'),
(1, 'CEE331'),
(1, 'CEE360'),
(2, 'CEE360'),
(4, 'CEE460'),
(2, 'CEE475'),
(4, 'LAW101'),
(2, 'LAW200');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `section_serial` varchar(10) NOT NULL,
  `time` varchar(20) DEFAULT NULL,
  `days` varchar(10) DEFAULT NULL,
  `classroom` varchar(20) DEFAULT NULL,
  `course_id` varchar(6) DEFAULT NULL,
  `seats` int(11) DEFAULT 40
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `section_serial`, `time`, `days`, `classroom`, `course_id`, `seats`) VALUES
(5001, '1', '04:20 PM - 05:50 PM', 'ST', 'NAC405', 'LAW101', 40),
(5002, '2', '09:40 AM - 11:10 AM', 'MW', 'NAC618', 'LAW101', 40),
(5003, '3', '01:00 PM - 02:30 PM', 'MW', 'NAC405', 'LAW101', 40),
(5004, '1', '09:40 AM - 11:10 AM', 'ST', 'NAC509', 'CEE370', 40),
(5005, '2', '08:00 AM - 09:30 AM', 'ST', 'SAC207', 'CEE370', 40),
(5006, '4', '01:00 PM - 02:30 PM', 'ST', 'NAC203', 'LAW101', 40),
(5007, '5', '04:20 PM - 05:50 PM', 'ST', 'NAC618', 'LAW101', 40),
(5008, '1', '09:40 AM - 11:10 AM', 'ST', 'NAC405', 'LAW200', 40),
(5009, '2', '08:00 AM - 09:30 AM', 'MW', 'NAC618', 'LAW200', 40),
(5010, '3', '02:40 PM - 04:10 PM', 'MW', 'NAC203', 'LAW200', 40),
(5011, '1', '09:40 AM - 11:10 AM', 'MW', 'NAC509', 'CEE310', 39),
(5012, '2', '11:20 AM - 12:50 PM', 'MW', 'NAC509', 'CEE310', 40),
(5013, '3', '02:40 PM - 04:10 PM', 'ST', 'NAC508', 'CEE310', 40),
(5014, '1', '09:40 AM - 11:10 AM', 'MW', 'OAT602', 'CEE330', 38),
(5015, '1', '08:00 AM - 09:30 AM', 'ST', 'SAC304', 'CEE331', 40),
(5016, '2', '09:40 AM - 11:10 AM', 'ST', 'NAC604', 'CEE331', 40),
(5017, '1', '02:40 PM - 04:10 PM', 'MW', 'OAT602', 'CEE335', 38),
(5018, '1', '01:00 PM - 02:30 PM', 'MW', 'SAC313', 'CEE340', 40),
(5019, '2', '02:40 PM - 04:10 PM', 'MW', 'SAC313', 'CEE340', 39),
(5020, '1', '08:00 AM - 09:30 AM', 'MW', 'SAC304', 'CEE350', 40),
(5021, '1', '11:20 AM - 12:50 PM', 'MW', 'SAC304', 'CEE360', 39),
(5022, '2', '01:00 PM - 02:30 PM', 'MW', 'SAC304', 'CEE360', 40),
(5023, '1', '02:40 PM - 05:50 PM', 'S', 'B118', 'CEE360', 40),
(5024, '1', '09:40 AM - 11:10 AM', 'ST', 'NAC509', 'CEE370', 40),
(5025, '2', '08:00 AM - 09:30 AM', 'ST', 'SAC207', 'CEE370', 40),
(5026, '1', '11:20 AM - 02:30 PM', 'R', 'B115', 'CEE371', 40),
(5027, '1', '11:20 AM - 12:50 PM', 'ST', 'NAC509', 'CEE373', 40),
(5028, '1', '11:20 AM - 02:30 PM', 'R', 'LAB1', 'CEE373', 40),
(5029, '1', '09:40 AM - 11:10 AM', 'MW', 'SAC304', 'CEE415', 40),
(5030, '1', '02:40 PM - 04:10 PM', 'ST', 'SAC304', 'CEE430', 40),
(5031, '2', '01:00 PM - 02:30 PM', 'ST', 'SAC313', 'CEE430', 40),
(5032, '1', '02:40 PM - 04:10 PM', 'ST', 'SAC207', 'CEE431', 40),
(5033, '1', '08:00 AM - 11:10 AM', 'R', 'SAC207', 'CEE435', 40),
(5034, '1', '11:20 AM - 12:50 PM', 'ST', 'SAC313', 'CEE460', 40),
(5035, '1', '08:00 AM - 09:30 AM', 'MW', 'SAC207', 'CEE470', 40),
(5036, '1', '04:20 PM - 05:50 PM', 'MW', 'SAC313', 'CEE475', 40);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `Student_Id` int(11) NOT NULL,
  `First_name` varchar(50) DEFAULT NULL,
  `Last_name` varchar(50) DEFAULT NULL,
  `Student_contact` varchar(15) DEFAULT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Blood_group` varchar(5) DEFAULT NULL,
  `DOB` date DEFAULT NULL,
  `Father_name` varchar(100) DEFAULT NULL,
  `Mother_name` varchar(100) DEFAULT NULL,
  `Local_guardian` varchar(100) DEFAULT NULL,
  `Guardian_contact` varchar(15) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `SSC_year` year(4) DEFAULT NULL,
  `SSC_gpa` decimal(3,2) DEFAULT NULL,
  `SSC_roll` int(11) DEFAULT NULL,
  `SSC_reg` int(11) DEFAULT NULL,
  `SSC_in` varchar(100) DEFAULT NULL,
  `HSC_year` year(4) DEFAULT NULL,
  `HSC_gpa` decimal(3,2) DEFAULT NULL,
  `HSC_roll` int(11) DEFAULT NULL,
  `HSC_reg` int(11) DEFAULT NULL,
  `HSC_in` varchar(100) DEFAULT NULL,
  `Dept_ID` int(11) DEFAULT NULL,
  `Chair_ID` int(11) DEFAULT NULL,
  `Student_picture` varchar(255) DEFAULT NULL,
  `Student_signature` varchar(255) DEFAULT NULL,
  `SSC_certificate` varchar(255) DEFAULT NULL,
  `SSC_transcript` varchar(255) DEFAULT NULL,
  `HSC_certificate` varchar(255) DEFAULT NULL,
  `HSC_transcript` varchar(255) DEFAULT NULL,
  `Student_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`Student_Id`, `First_name`, `Last_name`, `Student_contact`, `Gender`, `Blood_group`, `DOB`, `Father_name`, `Mother_name`, `Local_guardian`, `Guardian_contact`, `Address`, `SSC_year`, `SSC_gpa`, `SSC_roll`, `SSC_reg`, `SSC_in`, `HSC_year`, `HSC_gpa`, `HSC_roll`, `HSC_reg`, `HSC_in`, `Dept_ID`, `Chair_ID`, `Student_picture`, `Student_signature`, `SSC_certificate`, `SSC_transcript`, `HSC_certificate`, `HSC_transcript`, `Student_password`) VALUES
(1, 'Tawsif ', 'Ahmed ', '1609503461', 'Male', 'B+', '2003-11-29', 'Jamal Hossain ', 'Kamrun Nahar', 'Shantunu Barua ', '1601280402', 'House 330-31,Afroza Begum Road, Bashundhara R/A', '2020', 5.00, 102993, 1714405398, 'Chittagong Government Hogh School ', '2022', 5.00, 114179, 1714405398, 'Bakalia Governmnet College ', NULL, NULL, '../uploads/1756798786_IMG_9726.jpg', '../uploads/1756798786_tawsif.jpg', '../uploads/1756798786_rsz_img_9726.jpg', '../uploads/1756798786_T-table.png', '../uploads/1756798786_Slide28.JPG', '../uploads/1756798786_ChatGPT Image Jul 14, 2025, 02_12_22 AM.png', '$2y$10$iwAlOiWKq1ZgII5gvJ0.Y.dY2csbUAxx8mDG3tratyJaTz1Qqp/mC'),
(2, 'Sazid ', 'sdfssdfsd234', '123', 'Male', 'B+', '2025-08-06', 'asfas', 'sdasd', 'asdasd21312', '232323', 'dsfsdfsdfsd', '0000', 5.00, 2121, 112, 'fytfhgf', '0000', 4.00, 7764464, 446, 'uguhgjg', NULL, NULL, '../uploads/1756812706_rsz_img_9726.jpg', '../uploads/1756812706_T-table.png', '../uploads/1756812706_Logisim LAB 6.png', '../uploads/1756812706_TusharBhai.jpg', '../uploads/1756812706_cc04107e-f004-42ef-b62e-9b28b6b649cd.jpg', '../uploads/1756812706_tawsif.jpg', '$2y$10$S/HsnFcM3Bl7lHHmGQa5xe2Y5hQ.IdPzn/6OgHtGn2f9EJ.hLhg3S'),
(3, 'jomila', 'khandakar', '234234234', 'Female', 'A+', '2025-09-16', '435345', '345345', 'ertgert', '435345345', 'dfgdfgdfgdfsgsdg', '0000', 9.99, 43534534, 2147483647, 'fgvdfgdfg', '0000', 9.99, 34534534, 345345345, 'fdsgdfsgsdfg', NULL, NULL, '../uploads/1756882782_1754012435_Screenshot 2025-07-14 010828.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 020615.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 010326.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 014655.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 005934.png', '../uploads/1756882782_1754015717_Screenshot 2025-07-14 010326.png', NULL),
(4, 'Sifat', 'Ahmed', '34234234', 'Male', 'AB+', '2025-09-09', 'Amanullah Shah', 'sadfasdfsdf', 'asdfdsaf', '5467456456', 'House- 60 , Road-5/A, Block- C, Arambag R/A, Mirpur, Dhaka-1216', '0000', 5.00, 123123, 45645645, 'asdaD', '0000', 9.99, 456456, 2147483647, 'dsafaasdf', NULL, NULL, '../uploads/1757088106_1754015717_Screenshot 2025-07-14 010326.png', '../uploads/1757088106_1754015717_Screenshot 2025-07-14 011333.png', '../uploads/1757088106_1754012435_Screenshot 2025-07-14 010828.png', '../uploads/1757088106_1756452834_1754015717_Screenshot 2025-07-14 010326.png', '../uploads/1757088106_1754015717_Screenshot 2025-07-14 010828.png', '../uploads/1757088106_1754015717_Screenshot 2025-07-14 010326.png', '$2y$10$Qc8u3zF7JndSXEa0sW597eqKuoAVV8PLHB.P/Ve6He/l8s2TTdgzK');

-- --------------------------------------------------------

--
-- Table structure for table `takes`
--

CREATE TABLE `takes` (
  `student_id` int(4) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `takes`
--

INSERT INTO `takes` (`student_id`, `section_id`) VALUES
(1, 5011),
(1, 5017),
(2, 5014),
(4, 5019),
(4, 5021);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(4) NOT NULL,
  `teacher_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `teacher_type` varchar(20) NOT NULL,
  `contact` bigint(20) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `salary` decimal(7,2) NOT NULL,
  `address` varchar(100) NOT NULL,
  `department_id` int(3) NOT NULL,
  `chair_id` int(4) NOT NULL,
  `designation` varchar(30) NOT NULL,
  `dob` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `teacher_name`, `password`, `teacher_type`, `contact`, `gender`, `salary`, `address`, `department_id`, `chair_id`, `designation`, `dob`) VALUES
(2001, 'K. T. Rahman ', 'law123', 'FullTime', 1700111222, 'Male', 80000.00, 'Bashundhara R/A, Dhaka', 101, 25203, 'Assistant Professor', '1985-03-15'),
(2002, 'Sharmeen Akter', 'law456', 'PartTime', 1700111333, 'Female', 55000.00, 'Gulshan, Dhaka', 101, 25203, 'Lecturer', '1990-06-10'),
(2003, 'A. M. Wahid', 'cee123', 'FullTime', 1700222333, 'Male', 95000.00, 'Banani, Dhaka', 104, 25204, 'Associate Professor', '1980-12-05'),
(2004, 'Nazmul Uddin', 'cee456', 'PartTime', 1700222444, 'Male', 60000.00, 'Mirpur, Dhaka', 104, 25204, 'Lecturer', '1992-02-20'),
(2005, 'S. Q. Bashar', 'law789', 'FullTime', 1700111444, 'Male', 82000.00, 'Uttara, Dhaka', 101, 25203, 'Assistant Professor', '1986-07-20'),
(2006, 'T. A. Sayeed', 'law999', 'FullTime', 1700111555, 'Male', 88000.00, 'Mohakhali, Dhaka', 101, 25203, 'Associate Professor', '1982-04-25'),
(2007, 'N. Z. Ullah', 'cee789', 'FullTime', 1700222555, 'Male', 98000.00, 'Dhanmondi, Dhaka', 104, 25204, 'Professor', '1978-09-12'),
(2008, 'T. M. Yasin', 'cee999', 'FullTime', 1700222666, 'Male', 93000.00, 'Khilkhet, Dhaka', 104, 25204, 'Associate Professor', '1981-11-30'),
(2009, 'S. B. Chowdhury', 'cee555', 'FullTime', 1700222777, 'Male', 90000.00, 'Baridhara, Dhaka', 104, 25204, 'Assistant Professor', '1985-01-05'),
(2010, 'A. M. Wazed', 'cee321', 'FullTime', 1700222888, 'Male', 92000.00, 'Mirpur DOHS, Dhaka', 104, 25204, 'Associate Professor', '1983-03-18'),
(2011, 'N. L. Mostafa', 'cee654', 'PartTime', 1700222999, 'Male', 65000.00, 'Badda, Dhaka', 104, 25204, 'Lecturer', '1991-05-22'),
(2012, 'Nova Ahmed', 'nova123', 'FullTime', 112233445, 'Female', 99999.99, 'Somewhere in Bangladesh', 103, 25202, 'Professor', '1972-08-31'),
(2013, 'Nabil Bin Hannan', 'nabil123', 'FullTime', 7788994466, 'Male', 15000.00, 'Definitely not around NSU Campus', 103, 25202, 'Assistant Professor', '1989-06-06');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_slot`
--

CREATE TABLE `teacher_slot` (
  `Teacher_ID` int(11) NOT NULL,
  `Slot_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teaches`
--

CREATE TABLE `teaches` (
  `teacher_id` int(4) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teaches`
--

INSERT INTO `teaches` (`teacher_id`, `section_id`) VALUES
(2001, 5001),
(2001, 5002),
(2002, 5003),
(2003, 5004),
(2004, 5005);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`bank_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`Application_Id`);

--
-- Indexes for table `chairman`
--
ALTER TABLE `chairman`
  ADD PRIMARY KEY (`chair_id`),
  ADD UNIQUE KEY `department_id` (`department_id`);

--
-- Indexes for table `completed_course`
--
ALTER TABLE `completed_course`
  ADD PRIMARY KEY (`studentID`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `fk_course_department` (`department_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD UNIQUE KEY `contact` (`contact`);

--
-- Indexes for table `dept_program`
--
ALTER TABLE `dept_program`
  ADD PRIMARY KEY (`department_id`,`programs`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`Teacher_ID`,`Course_ID`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`faq_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `pre_requisite`
--
ALTER TABLE `pre_requisite`
  ADD PRIMARY KEY (`course_id`,`pr_id`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`course_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`Student_Id`),
  ADD KEY `Dept_ID` (`Dept_ID`),
  ADD KEY `Chair_ID` (`Chair_ID`);

--
-- Indexes for table `takes`
--
ALTER TABLE `takes`
  ADD PRIMARY KEY (`student_id`,`section_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `chair_id` (`chair_id`);

--
-- Indexes for table `teacher_slot`
--
ALTER TABLE `teacher_slot`
  ADD PRIMARY KEY (`Teacher_ID`,`Slot_ID`);

--
-- Indexes for table `teaches`
--
ALTER TABLE `teaches`
  ADD PRIMARY KEY (`teacher_id`,`section_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `Application_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chairman`
--
ALTER TABLE `chairman`
  MODIFY `chair_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25206;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `Student_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`Student_Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chairman`
--
ALTER TABLE `chairman`
  ADD CONSTRAINT `chairman_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `completed_course`
--
ALTER TABLE `completed_course`
  ADD CONSTRAINT `completed_course_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `student` (`Student_Id`),
  ADD CONSTRAINT `completed_course_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`);

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_course_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dept_program`
--
ALTER TABLE `dept_program`
  ADD CONSTRAINT `fk_deptprogram_department` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `enrollment_ibfk_1` FOREIGN KEY (`Teacher_ID`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faq`
--
ALTER TABLE `faq`
  ADD CONSTRAINT `faq_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`Student_Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `faq_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`Student_Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`Dept_ID`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`Chair_ID`) REFERENCES `chairman` (`chair_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `takes`
--
ALTER TABLE `takes`
  ADD CONSTRAINT `takes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`Student_Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `teacher_ibfk_2` FOREIGN KEY (`chair_id`) REFERENCES `chairman` (`chair_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher_slot`
--
ALTER TABLE `teacher_slot`
  ADD CONSTRAINT `teacher_slot_ibfk_1` FOREIGN KEY (`Teacher_ID`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teaches`
--
ALTER TABLE `teaches`
  ADD CONSTRAINT `teaches_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
