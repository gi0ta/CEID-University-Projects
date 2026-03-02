-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: localhost
-- Χρόνος δημιουργίας: 08 Ιουν 2025 στις 14:38:24
-- Έκδοση διακομιστή: 10.4.32-MariaDB
-- Έκδοση PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `diploma_db`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `committee_invitations`
--

CREATE TABLE `committee_invitations` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `inviting_professor_id` int(11) NOT NULL,
  `invited_professor_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `invitation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `committee_members`
--

CREATE TABLE `committee_members` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `invitation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_date` timestamp NULL DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `committee_members`
--

INSERT INTO `committee_members` (`id`, `thesis_assignment_id`, `professor_id`, `invitation_date`, `response_date`, `status`) VALUES
(1, 2, 10, '2025-01-14 22:00:00', '2025-01-19 22:00:00', 'accepted'),
(2, 2, 6, '2025-01-14 22:00:00', '2025-01-16 22:00:00', 'accepted'),
(3, 3, 9, '2024-11-14 22:00:00', '2024-11-16 22:00:00', 'accepted'),
(4, 3, 8, '2024-11-14 22:00:00', '2024-11-19 22:00:00', 'accepted'),
(5, 4, 5, '2024-07-14 21:00:00', '2024-07-16 21:00:00', 'accepted'),
(6, 4, 3, '2024-07-14 21:00:00', '2024-07-16 21:00:00', 'accepted'),
(7, 5, 6, '2025-04-14 21:00:00', '2025-04-18 21:00:00', 'accepted'),
(8, 5, 4, '2025-04-14 21:00:00', '2025-04-18 21:00:00', 'accepted'),
(9, 6, 2, '2024-12-14 22:00:00', '2024-12-18 22:00:00', 'accepted'),
(10, 6, 5, '2024-12-14 22:00:00', '2024-12-19 22:00:00', 'accepted');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `examination_protocols`
--

CREATE TABLE `examination_protocols` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `protocol_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `protocol_number` varchar(50) DEFAULT NULL,
  `protocol_html` text DEFAULT NULL,
  `protocol_pdf` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `examination_protocols`
--

INSERT INTO `examination_protocols` (`id`, `thesis_assignment_id`, `protocol_date`, `protocol_number`, `protocol_html`, `protocol_pdf`, `created_by`) VALUES
(3, 4, '2025-06-01 17:46:24', 'PROT-2025-004', '<p>Πρωτόκολλο εξέτασης διπλωματικής εργασίας.</p><p>Η τριμελής επιτροπή αποφάσισε την επιτυχή ολοκλήρωση της διπλωματικής εργασίας με βαθμό 9.5/10.</p><p>..</p>', 'protocol_thesis4.pdf', 1);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `gs_documents`
--

CREATE TABLE `gs_documents` (
  `id` int(11) NOT NULL,
  `gs_number` varchar(50) NOT NULL,
  `gs_year` varchar(10) NOT NULL,
  `document_file` varchar(255) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `professors`
--

CREATE TABLE `professors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `university` varchar(100) DEFAULT NULL,
  `specialty` varchar(200) DEFAULT NULL,
  `topic` varchar(200) DEFAULT NULL,
  `office` varchar(50) DEFAULT NULL,
  `landline_phone` varchar(20) DEFAULT NULL,
  `mobile_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `professors`
--

INSERT INTO `professors` (`id`, `user_id`, `department`, `university`, `specialty`, `topic`, `office`, `landline_phone`, `mobile_phone`) VALUES
(1, 1, 'CEID', 'University of Patras', '', 'Network-centric systems', NULL, '2610996915', '6977998877'),
(2, 2, 'CEID', 'University of Patras', '', 'Integrated Systems', NULL, '2610885511', '6988812345'),
(3, 3, 'CEID', 'University of Patras', '', 'Artificial Intelligence', NULL, '23', '545'),
(4, 4, 'CEID', 'University of Patras', '', 'WEB', NULL, '34', '245'),
(5, 5, 'CEID', 'University of Patras', '', 'Artificial Intelligence', NULL, '2610170390', '6917031990'),
(6, 6, 'IT', 'University of Patras', '', 'Data Engineering', NULL, '2610324365', '6978530352'),
(7, 7, 'CEID', 'University of Patras', '', 'informatics', NULL, '2610324242', '6934539920'),
(8, 8, 'Arxeologias', 'UOI', '', 'Arxeologia', NULL, '2610945934', '6947845334'),
(9, 9, 'Economics', 'UOA', '', 'Business', NULL, '2310231023', '6929349285'),
(10, 10, 'Engineering', 'University of SKG', '', 'SQL injections', NULL, '1234567890', '6988223322'),
(11, 32, 'Τμήμα Μηχανικών Η/Υ και Πληροφορικής', NULL, 'Τεχνητή Νοημοσύνη', NULL, NULL, NULL, NULL),
(12, 33, 'Τμήμα Μηχανικών Η/Υ και Πληροφορικής', NULL, 'Βάσεις Δεδομένων', NULL, NULL, NULL, NULL),
(13, 34, 'Τμήμα Μηχανικών Η/Υ και Πληροφορικής', NULL, 'Δίκτυα Υπολογιστών', NULL, NULL, NULL, NULL),
(14, 35, 'Τμήμα Μηχανικών Η/Υ και Πληροφορικής', NULL, 'Ασφάλεια Πληροφοριακών Συστημάτων', NULL, NULL, NULL, NULL),
(15, 36, 'Τμήμα Μηχανικών Η/Υ και Πληροφορικής', NULL, 'Λογισμικό και Αλγόριθμοι', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `professor_notes`
--

CREATE TABLE `professor_notes` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `note_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_private` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `public_presentation_announcements`
--

CREATE TABLE `public_presentation_announcements` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `supervisor_name` varchar(255) NOT NULL,
  `presentation_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location` varchar(255) DEFAULT NULL,
  `online_link` varchar(255) DEFAULT NULL,
  `announcement_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `public_presentation_announcements`
--

INSERT INTO `public_presentation_announcements` (`id`, `thesis_assignment_id`, `title`, `student_name`, `supervisor_name`, `presentation_date`, `location`, `online_link`, `announcement_text`, `created_at`) VALUES
(1, 3, 'Σχεδιασμός και ανάπτυξη NoSQL βάσης δεδομένων για Big Data', 'Petros Verikokos', 'Basilis Karras', '2025-06-13 17:31:38', NULL, 'https://upatras-gr.zoom.us/j/28789295755', 'Σας προσκαλούμε στην παρουσίαση της διπλωματικής εργασίας του/της Petros Verikokos με θέμα \"Σχεδιασμός και ανάπτυξη NoSQL βάσης δεδομένων για Big Data\". Η παρουσίαση θα πραγματοποιηθεί διαδικτυακά στις 13/06/2025 20:31 μέσω του συνδέσμου: https://upatras-gr.zoom.us/j/28789295755.', '2025-05-14 18:31:38'),
(2, 4, 'Βελτιστοποίηση ερωτημάτων SQL σε σχεσιακές βάσεις δεδομένων', 'test name', 'Eleni Voyiatzaki', '2025-04-19 21:00:00', NULL, 'https://upatras-gr.zoom.us/j/67004661992', 'Σας προσκαλούμε στην παρουσίαση της διπλωματικής εργασίας του/της test name με θέμα \"Βελτιστοποίηση ερωτημάτων SQL σε σχεσιακές βάσεις δεδομένων\". Η παρουσίαση θα πραγματοποιηθεί διαδικτυακά στις 20/04/2025 00:00 μέσω του συνδέσμου: https://upatras-gr.zoom.us/j/67004661992.', '2025-05-14 18:31:38');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `status_changes`
--

CREATE TABLE `status_changes` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `status_changes`
--

INSERT INTO `status_changes` (`id`, `thesis_assignment_id`, `old_status`, `new_status`, `changed_at`, `changed_by`, `notes`) VALUES
(1, 1, NULL, 'pending', '2025-05-14 18:31:38', 1, 'Αρχική ανάθεση θέματος'),
(2, 2, NULL, 'pending', '2025-05-14 18:31:38', 2, 'Αρχική ανάθεση θέματος'),
(3, 2, 'pending', 'active', '2025-05-14 18:31:38', 2, 'Ενεργοποίηση διπλωματικής μετά την αποδοχή από την τριμελή'),
(4, 3, NULL, 'pending', '2025-05-14 18:31:38', 3, 'Αρχική ανάθεση θέματος'),
(5, 3, 'pending', 'active', '2025-05-14 18:31:38', 3, 'Ενεργοποίηση διπλωματικής μετά την αποδοχή από την τριμελή'),
(6, 3, 'active', 'examination', '2025-05-14 18:31:38', 3, 'Η διπλωματική είναι έτοιμη για εξέταση'),
(7, 4, NULL, 'pending', '2025-05-14 18:31:38', 4, 'Αρχική ανάθεση θέματος'),
(8, 4, 'pending', 'active', '2025-05-14 18:31:38', 4, 'Ενεργοποίηση διπλωματικής μετά την αποδοχή από την τριμελή'),
(9, 4, 'active', 'examination', '2025-05-14 18:31:38', 4, 'Η διπλωματική είναι έτοιμη για εξέταση'),
(10, 4, 'examination', 'completed', '2025-05-14 18:31:38', 31, 'Η διπλωματική ολοκληρώθηκε επιτυχώς'),
(11, 5, NULL, 'pending', '2025-05-14 18:31:38', 5, 'Αρχική ανάθεση θέματος'),
(12, 5, 'pending', 'active', '2025-05-14 18:31:38', 5, 'Ενεργοποίηση διπλωματικής μετά την αποδοχή από την τριμελή'),
(13, 5, 'active', 'cancelled', '2025-05-14 18:31:38', 5, 'Ακύρωση διπλωματικής λόγω μη προόδου του φοιτητή'),
(14, 6, NULL, 'pending', '2025-05-14 18:31:38', 6, 'Αρχική ανάθεση θέματος'),
(15, 6, 'pending', 'active', '2025-05-14 18:31:38', 6, 'Ενεργοποίηση διπλωματικής μετά την αποδοχή από την τριμελή');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `street_number` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `mobile_phone` varchar(20) DEFAULT NULL,
  `home_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `students`
--

INSERT INTO `students` (`id`, `user_id`, `registration_number`, `father_name`, `semester`, `address`, `street`, `street_number`, `city`, `postcode`, `mobile_phone`, `home_phone`) VALUES
(1, 11, '10433999', 'Orestis', 10, NULL, 'test street', '45', 'test city', '39955', '6939096979', '2610333000'),
(2, 12, '10434000', 'George', 12, NULL, 'Ermou', '18', 'Athens', '10431', '6970001112', '2610123456'),
(3, 13, '10434001', 'Giannis', 8, NULL, 'Adrianou', '20', 'Thessaloniki', '54248', '6970001112', '2610778899'),
(4, 14, '10434002', 'father', 12, NULL, 'str', '1', 'patra', '26222', '6912345678', '2610123456'),
(5, 15, '10434003', 'Alex', 12, NULL, 'Fascination', '17', 'London', '1989', '6902051989', '2610251989'),
(6, 16, '10434004', 'Daspletosaurus', 7, NULL, 'Cretaceous', '2', 'Laramidia', '54321', '6911231234', '2610432121'),
(7, 17, '10434005', 'Paul', 10, NULL, 'Smith Str.', '33', 'New York ', '59', '-', '-'),
(8, 18, '10434006', 'José ', 7, NULL, 'Johnson', '90', 'New York ', '70', '-', '-'),
(9, 19, '10434007', 'Douglas', 12, NULL, 'Sortef', '29', 'New York', '26', '-', '-'),
(10, 20, '10434008', 'none', 11, NULL, 'Groove Str.', '23', 'Los Angeles', '1', '-', '-'),
(11, 21, '10434009', 'Jess ', 11, NULL, 'Magic Str. ', '8', 'New Orleans', '35', '67', '56'),
(12, 22, '10434010', 'Paul', 8, NULL, 'Substance Str.', '25', 'Los Angeles ', '7', '90', '67'),
(13, 23, '10434011', 'Lee', 8, NULL, 'Pearl Str. ', '4', 'Michigan', '8', '-', '-'),
(14, 24, '10434012', '-', 10, NULL, 'Midsommar Str. l', '1', 'Away', '24', '2', '5'),
(15, 25, '10434013', 'Ray', 8, NULL, 'Lonely Str.', '27', 'Bridport', '-7', '43', '56'),
(16, 26, '10434014', 'Eduardo ', 8, NULL, 'Almadovar', '55', 'Madrid', '23', '4', '5'),
(17, 27, '10434015', 'none', 9, NULL, 'Poor Str.', '3', 'Paris ', '34', '4455555', '2333333'),
(18, 28, '10434016', 'Basil', 7, NULL, 'Mpouat Str.', '23', 'Athens', '10', '45', '09'),
(19, 29, '10434017', 'Sami', 8, NULL, 'Desperado Str. ', '24', 'Madrid ', '656', '221', '344'),
(20, 30, '10434018', 'Kieślowski', 7, NULL, 'Before Str.', '36', 'Paris', '567', '3455', '1223'),
(21, 37, '1234567', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 38, '1234568', NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 39, '1234569', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 40, '1234570', NULL, 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 41, '1234571', NULL, 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesis_assignments`
--

CREATE TABLE `thesis_assignments` (
  `id` int(11) NOT NULL,
  `thesis_topic_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `status` enum('pending','active','examination','completed','cancelled') DEFAULT 'pending',
  `assignment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline_date` timestamp NULL DEFAULT NULL,
  `completion_date` timestamp NULL DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `gs_number` varchar(50) DEFAULT NULL,
  `gs_year` varchar(10) DEFAULT NULL,
  `final_grade` decimal(3,1) DEFAULT NULL,
  `examination_protocol_generated` tinyint(1) DEFAULT 0,
  `repository_link` varchar(255) DEFAULT NULL,
  `cancellation_date` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `thesis_assignments`
--

INSERT INTO `thesis_assignments` (`id`, `thesis_topic_id`, `student_id`, `supervisor_id`, `status`, `assignment_date`, `deadline_date`, `completion_date`, `duration_days`, `gs_number`, `gs_year`, `final_grade`, `examination_protocol_generated`, `repository_link`, `cancellation_date`, `cancellation_reason`) VALUES
(1, 1, 1, 1, 'pending', '2024-11-13 22:00:00', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(2, 2, 2, 2, 'active', '2025-01-13 22:00:00', NULL, NULL, NULL, '100', '2025', NULL, 0, NULL, NULL, NULL),
(3, 3, 3, 3, 'examination', '2024-11-13 22:00:00', NULL, NULL, NULL, '110', '2025', NULL, 0, NULL, NULL, NULL),
(4, 4, 4, 4, 'completed', '2024-07-13 21:00:00', NULL, '2025-04-20 21:00:00', NULL, '166', '2025', 6.0, 0, 'https://nemertes.library.upatras.gr/thesis/89525', NULL, NULL),
(5, 5, 5, 5, 'cancelled', '2025-04-13 21:00:00', NULL, NULL, NULL, '130', '2025', NULL, 0, NULL, '2025-04-26 21:00:00', 'Μη πρόοδος του φοιτητή'),
(6, 6, 6, 6, 'active', '2024-12-13 22:00:00', NULL, NULL, NULL, '185', '2025', NULL, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesis_external_links`
--

CREATE TABLE `thesis_external_links` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `link_url` varchar(255) NOT NULL,
  `link_title` varchar(255) NOT NULL,
  `link_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesis_files`
--

CREATE TABLE `thesis_files` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('draft','final','supplementary','presentation','code','data') NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `thesis_files`
--

INSERT INTO `thesis_files` (`id`, `thesis_assignment_id`, `file_name`, `file_path`, `file_type`, `upload_date`, `description`) VALUES
(15, 3, 'final_report_thesis3.pdf', 'thesis_files/thesis3_final_report.pdf', 'final', '2025-06-01 17:46:24', 'Τελική αναφορά διπλωματικής εργασίας'),
(16, 3, 'presentation_thesis3.pptx', 'thesis_files/thesis3_presentation.pptx', 'presentation', '2025-06-01 17:46:24', 'Παρουσίαση διπλωματικής εργασίας'),
(17, 4, 'final_report_thesis4.pdf', 'thesis_files/thesis4_final_report.pdf', 'final', '2025-06-01 17:46:24', 'Τελική αναφορά διπλωματικής εργασίας'),
(18, 4, 'presentation_thesis4.pptx', 'thesis_files/thesis4_presentation.pptx', 'presentation', '2025-06-01 17:46:24', 'Παρουσίαση διπλωματικής εργασίας'),
(19, 4, 'source_code_thesis4.zip', 'thesis_files/thesis4_code.zip', 'code', '2025-06-01 17:46:24', 'Πηγαίος κώδικας διπλωματικής εργασίας'),
(20, 4, 'dataset_thesis4.csv', 'thesis_files/thesis4_data.csv', 'data', '2025-06-01 17:46:24', 'Δεδομένα που χρησιμοποιήθηκαν στη διπλωματική εργασία');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesis_grades`
--

CREATE TABLE `thesis_grades` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `subject_understanding` decimal(3,1) DEFAULT NULL,
  `problem_solving` decimal(3,1) DEFAULT NULL,
  `literature_review` decimal(3,1) DEFAULT NULL,
  `methodology` decimal(3,1) DEFAULT NULL,
  `implementation` decimal(3,1) DEFAULT NULL,
  `innovation` decimal(3,1) DEFAULT NULL,
  `writing_quality` decimal(3,1) DEFAULT NULL,
  `presentation` decimal(3,1) DEFAULT NULL,
  `final_grade` decimal(3,1) NOT NULL,
  `comments` text DEFAULT NULL,
  `grading_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `thesis_grades`
--

INSERT INTO `thesis_grades` (`id`, `thesis_assignment_id`, `professor_id`, `subject_understanding`, `problem_solving`, `literature_review`, `methodology`, `implementation`, `innovation`, `writing_quality`, `presentation`, `final_grade`, `comments`, `grading_date`) VALUES
(1, 4, 4, 10.0, 9.0, NULL, NULL, 10.0, NULL, NULL, 9.0, 9.5, NULL, '2025-05-14 18:31:38'),
(2, 4, 5, 8.0, 10.0, NULL, NULL, 8.0, NULL, NULL, 9.0, 8.8, NULL, '2025-05-14 18:31:38'),
(3, 4, 3, 9.0, 9.0, NULL, NULL, 10.0, NULL, NULL, 9.0, 9.3, NULL, '2025-05-14 18:31:38');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesis_presentations`
--

CREATE TABLE `thesis_presentations` (
  `id` int(11) NOT NULL,
  `thesis_assignment_id` int(11) NOT NULL,
  `presentation_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location` varchar(255) DEFAULT NULL,
  `online_link` varchar(255) DEFAULT NULL,
  `presentation_type` enum('physical','online','hybrid') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `thesis_presentations`
--

INSERT INTO `thesis_presentations` (`id`, `thesis_assignment_id`, `presentation_date`, `location`, `online_link`, `presentation_type`, `created_at`, `updated_at`) VALUES
(1, 3, '2025-06-13 17:31:38', NULL, 'https://upatras-gr.zoom.us/j/28789295755', 'online', '2025-05-14 18:31:38', NULL),
(2, 4, '2025-04-19 21:00:00', NULL, 'https://upatras-gr.zoom.us/j/67004661992', 'online', '2025-05-14 18:31:38', NULL);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `thesis_topics`
--

CREATE TABLE `thesis_topics` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `description_file` varchar(255) DEFAULT NULL,
  `description_file_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `is_assigned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `thesis_topics`
--

INSERT INTO `thesis_topics` (`id`, `professor_id`, `title`, `summary`, `description_file`, `description_file_type`, `created_at`, `updated_at`, `is_assigned`) VALUES
(1, 1, 'Ανάπτυξη συστήματος αναγνώρισης προσώπου με χρήση βαθιάς μάθησης', 'Σκοπός της διπλωματικής είναι η ανάπτυξη ενός συστήματος αναγνώρισης προσώπου με χρήση τεχνικών βαθιάς μάθησης και συνελικτικών νευρωνικών δικτύων.', NULL, NULL, '2025-05-14 18:31:38', '2025-05-14 18:31:38', 1),
(2, 2, 'Εφαρμογή μηχανικής μάθησης για την πρόβλεψη τιμών μετοχών', 'Η διπλωματική στοχεύει στην ανάπτυξη ενός συστήματος πρόβλεψης τιμών μετοχών με χρήση αλγορίθμων μηχανικής μάθησης και ανάλυσης χρονοσειρών.', NULL, NULL, '2025-05-14 18:31:38', '2025-05-14 18:31:38', 1),
(3, 3, 'Σχεδιασμός και ανάπτυξη NoSQL βάσης δεδομένων για Big Data', 'Αντικείμενο της διπλωματικής είναι ο σχεδιασμός και η ανάπτυξη μιας NoSQL βάσης δεδομένων για την αποθήκευση και επεξεργασία μεγάλου όγκου δεδομένων.', NULL, NULL, '2025-05-14 18:31:38', '2025-05-14 18:31:38', 1),
(4, 4, 'Βελτιστοποίηση ερωτημάτων SQL σε σχεσιακές βάσεις δεδομένων', 'Η διπλωματική εστιάζει στη μελέτη και εφαρμογή τεχνικών βελτιστοποίησης ερωτημάτων SQL για την αύξηση της απόδοσης σχεσιακών βάσεων δεδομένων.', NULL, NULL, '2025-05-14 18:31:38', '2025-05-14 18:31:38', 1),
(5, 5, 'Ανάπτυξη συστήματος ασφαλούς επικοινωνίας με χρήση κρυπτογραφίας', 'Σκοπός της διπλωματικής είναι η ανάπτυξη ενός συστήματος ασφαλούς επικοινωνίας με χρήση σύγχρονων κρυπτογραφικών αλγορίθμων.', NULL, NULL, '2025-05-14 18:31:38', '2025-05-14 18:31:38', 1),
(6, 6, 'Μελέτη και υλοποίηση πρωτοκόλλων δρομολόγησης σε ασύρματα δίκτυα αισθητήρων', 'Η διπλωματική στοχεύει στη μελέτη και υλοποίηση πρωτοκόλλων δρομολόγησης για την αποδοτική επικοινωνία σε ασύρματα δίκτυα αισθητήρων.', NULL, NULL, '2025-05-14 18:31:38', '2025-05-14 18:31:38', 1),
(7, 7, 'Ανάπτυξη εφαρμογής για την παρακολούθηση και διαχείριση πόρων σε περιβάλλον cloud', 'Αντικείμενο της διπλωματικής είναι η ανάπτυξη μιας εφαρμογής για την παρακολούθηση και διαχείριση πόρων σε περιβάλλον υπολογιστικού νέφους.', NULL, NULL, '2025-05-14 18:31:38', NULL, 0),
(8, 8, 'Μελέτη και υλοποίηση τεχνικών εικονικοποίησης για βελτίωση της απόδοσης συστημάτων', 'Η διπλωματική εστιάζει στη μελέτη και υλοποίηση τεχνικών εικονικοποίησης για τη βελτίωση της απόδοσης υπολογιστικών συστημάτων.', NULL, NULL, '2025-05-14 18:31:38', NULL, 0),
(9, 9, 'Ανάπτυξη αλγορίθμων για την επίλυση προβλημάτων βελτιστοποίησης σε γραφήματα', 'Σκοπός της διπλωματικής είναι η ανάπτυξη και αξιολόγηση αλγορίθμων για την επίλυση προβλημάτων βελτιστοποίησης σε γραφήματα.', NULL, NULL, '2025-05-14 18:31:38', NULL, 0),
(10, 10, 'Μελέτη και υλοποίηση παράλληλων αλγορίθμων για επεξεργασία μεγάλου όγκου δεδομένων', 'Η διπλωματική στοχεύει στη μελέτη και υλοποίηση παράλληλων αλγορίθμων για την αποδοτική επεξεργασία μεγάλου όγκου δεδομένων.', NULL, NULL, '2025-05-14 18:31:38', NULL, 0),
(12, 2, 'test2', 'test.', 'uploads/thesis_descriptions/thesis_683f414cce33e_1748975948.pdf', NULL, '2025-06-03 18:39:08', '2025-06-08 11:40:39', 0),
(24, 3, 'test3', 'fd', NULL, NULL, '2025-06-07 16:28:30', '2025-06-07 16:28:30', 0);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('student','professor','secretary') NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Άδειασμα δεδομένων του πίνακα `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `first_name`, `last_name`, `created_at`, `last_login`, `is_active`) VALUES
(1, 'andreas.komninos', '$2y$10$txJtiahSBOPFA1uCRnY6tOtWzC0ItxSdwVzEqZ7WqLKSht1xi/A9G', 'akomninos@ceid.upatras.gr', 'professor', 'Andreas', 'Komninos', '2025-05-14 18:31:36', '2025-06-07 16:50:33', 1),
(2, 'vasilis.foukaras', '$2y$10$rdrW2Le/PIzZkup07KLwhen1LcjRj79Ww6lCKpn8h7BMzRhbfFnia', 'vasfou@ceid.upatras.gr', 'professor', 'Vasilis', 'Foukaras', '2025-05-14 18:31:36', '2025-06-08 09:56:53', 1),
(3, 'basilis.karras', '$2y$10$TvmTMZjE6KrfQHTe83cRIuK02FiVtnZL/7D1G.MCE/VlWhmzy90zC', 'karras@nterti.com', 'professor', 'Basilis', 'Karras', '2025-05-14 18:31:37', '2025-06-07 16:30:28', 1),
(4, 'eleni.voyiatzaki', '$2y$10$YpxvRQDbfRh6IsRRyyoWCuBdW7vCPBBE3kidEeDdvJNeiiZlVXhgG', 'eleni@ceid.gr', 'professor', 'Eleni', 'Voyiatzaki', '2025-05-14 18:31:37', '2025-06-07 16:50:40', 1),
(5, 'andrew.hozier byrne', '$2y$10$AiyRWixGNlnSuDfA.fCyZeQYZfu1n882Rtx5sdSOmjLiuRyl4PqK.', 'hozier@ceid.upatras.gr', 'professor', 'Andrew', 'Hozier Byrne', '2025-05-14 18:31:37', NULL, 1),
(6, 'nikos.korobos', '$2y$10$MwKAmajKAKJmrWT5U6s8beKx47py5fIJ/vR7Azz/hum7ozVKN0nla', 'nikos.korobos12@gmail.com', 'professor', 'Nikos', 'Korobos', '2025-05-14 18:31:37', '2025-06-01 18:13:13', 1),
(7, 'kostas.karanikolos', '$2y$10$76kUTAm4pVC4zOZzEsOZcOdj8CRjQ7aWFw0igfntEfSkZBHA1B0dq', 'kostkaranik@gmail.com', 'professor', 'Kostas', 'Karanikolos', '2025-05-14 18:31:37', NULL, 1),
(8, 'mpampis.sougias', '$2y$10$eV3F66EdHTXItt9c3X89iOgM7uRCDjPlvfLUb1IZV.S6cXCzAXzx.', 'mpampis123@gmail.com', 'professor', 'Mpampis', 'Sougias', '2025-05-14 18:31:37', NULL, 1),
(9, 'daskalos.makaveli', '$2y$10$ra4OrrjyvUOFO7TytkVfFuUIOmqZpNbukldjO0WiBbDRO7vSk.sMu', 'makavelibet@gmail.com', 'professor', 'Daskalos', 'Makaveli', '2025-05-14 18:31:37', NULL, 1),
(10, 'maria.palami', '$2y$10$Ho/nzJG6/DPmpZIxdWFzPOkTXWZn6Gamgo.H9kNnV60EYeDyFkcFq', 'palam@upatras.gr', 'professor', 'Maria', 'Palami', '2025-05-14 18:31:37', NULL, 1),
(11, 'st10433999', '$2y$10$D5KHeVcnJMHw1C3yhDcgB.A.fbCqMaritFoKCVhLEIGHYAA9TGMCm', '104333999@students.upatras.gr', 'student', 'Makis', 'Makopoulos', '2025-05-14 18:31:37', '2025-06-08 12:34:57', 1),
(12, 'st10434000', '$2y$10$hKGQpSxymjmnymY0HxAnXO1EJOcxf.bpbGtGt3.FR0.2dRm7uNdzW', 'st10434000@upnet.gr', 'student', 'John', 'Lennon', '2025-05-14 18:31:37', '2025-06-07 16:01:20', 1),
(13, 'st10434001', '$2y$10$q3AAJoPa0kTdwsXzHwl4.utzrU1.y3V1qISlLd1Kkf07kJtE2ef0.', 'st10434001@upnet.gr', 'student', 'Petros', 'Verikokos', '2025-05-14 18:31:37', NULL, 1),
(14, 'st10434002', '$2y$10$7vtJyYrfPhH3BvjC.QUbu.97hnjWeMvwGSYSBJl.wpnbA3dWEuXia', 'st10434002@upnet.gr', 'student', 'test', 'name', '2025-05-14 18:31:37', '2025-06-07 09:08:06', 1),
(15, 'st10434003', '$2y$10$nrNycGJ8fCMrNNjcoJf38eQ5XD4aJU.uYYWzPVg9w7rkDAA1dwjfC', 'st10434003@upnet.gr', 'student', 'Robert', 'Smith', '2025-05-14 18:31:37', NULL, 1),
(16, 'st10434004', '$2y$10$e10Pl5ofQ5fDPcoY28ekJuefFsi5PlNgi7RfFS99a3ZegqjYCgPT2', 'st10434004@upnet.gr', 'student', 'Rex', 'Tyrannosaurus', '2025-05-14 18:31:37', NULL, 1),
(17, 'st10434005', '$2y$10$SYwWLAvxl.B0TGLi5xKm3u26EGQ6ZwGfRMuOe4hi0pJvSIfDwxrIK', 'st10434005@upnet.gr', 'student', 'Paul', 'Mescal ', '2025-05-14 18:31:37', NULL, 1),
(18, 'st10434006', '$2y$10$qvp1ATXqXi3QPxINFnuv2OJCDZbS0xTT.4jCu1tX7tjJuu./IfR3u', 'st10434006@upnet.gr', 'student', 'Pedro', 'Pascal', '2025-05-14 18:31:37', NULL, 1),
(19, 'st10434007', '$2y$10$6sVKhhHCM5RuisdvYN5x3.inrc6f/dypKdikQBx4OsBwy1VrKKibq', 'st10434007@upnet.gr', 'student', 'David', 'Gilmour', '2025-05-14 18:31:37', NULL, 1),
(20, 'st10434008', '$2y$10$7D0eh5zyKN.DEdVwaymq/.oQyDH0BrfLy0LDDIF4Ov2UWpBBPO4fC', 'st10434008@upnet.gr', 'student', 'Lana', 'Del Rey ', '2025-05-14 18:31:37', NULL, 1),
(21, 'st10434009', '$2y$10$fi//gF48kgZ10pQRipjqO.QXjFjgREfomD3tSuu9Mc7UCP24xY/.K', 'st10434009@upnet.gr', 'student', 'Stevie', 'Nicks', '2025-05-14 18:31:38', NULL, 1),
(22, 'st10434010', '$2y$10$Z9vZtntrQxcByq0PHgrHNegMZCeIWD0TfiEmUpkbPg7HiUaSjDx.m', 'st10434010@upnet.gr', 'student', 'Margaret', 'Qualley', '2025-05-14 18:31:38', NULL, 1),
(23, 'st10434011', '$2y$10$4agZN.PEd37Q5ZIHQaFjEu7PGRTyxbfvNaWlhT58tfHSv2yM.D1qq', 'st10434011@upnet.gr', 'student', 'Mia', 'Goth', '2025-05-14 18:31:38', NULL, 1),
(24, 'st10434012', '$2y$10$F9v1eri9wsQ..MCBcd1WuO0rkGNxdxVrKoke3nOC794RnjvfVv22S', 'st10434012@upnet.gr', 'student', 'Florence ', 'Pugh', '2025-05-14 18:31:38', NULL, 1),
(25, 'st10434013', '$2y$10$K1nBD7grXApdtl9hjKXBQejRggJIt3nJqDDD0jvHDX2I3j7YpYqCm', 'st10434013@upnet.gr', 'student', 'PJ ', 'Harvey', '2025-05-14 18:31:38', NULL, 1),
(26, 'st10434014', '$2y$10$yDxqFxvv8QdvSOV9EjBDv.YCdBjWswRmfMBPiXIssKFquK8xs22GK', 'st10434014@upnet.gr', 'student', 'Penélope', 'Cruz', '2025-05-14 18:31:38', NULL, 1),
(27, 'st10434015', '$2y$10$n9DX8xawjAk1yCEbxqopiOrnDgsPckZNeQ339qtzO1XzbXa2aimWe', 'st10434015@upnet.gr', 'student', 'Emma', 'Stone', '2025-05-14 18:31:38', NULL, 1),
(28, 'st10434016', '$2y$10$/aDNRx20fPPaobauyCvFSONacEGXl2C.rA3VM5YMo6lhF1s384cPO', 'st10434016@upnet.gr', 'student', 'Jenny', 'Vanou', '2025-05-14 18:31:38', NULL, 1),
(29, 'st10434017', '$2y$10$g91KOdorBNyzsXjOiUM9SelwljL9edvN3zYGAI2/AafhlivySU.ci', 'st10434017@upnet.gr', 'student', 'Salma ', 'Hayek', '2025-05-14 18:31:38', NULL, 1),
(30, 'st10434018', '$2y$10$Cv4O57Xr/9U.NBDxTTXJjOyycBKEbGEBz74Pk1.glDjIWSNZNhqUG', 'st10434018@upnet.gr', 'student', 'Julie ', 'Delpy', '2025-05-14 18:31:38', NULL, 1),
(31, 'secretary', '$2y$10$R3XNDySUqHSTiBIbxTE/UubgHZHip5ks.lRrSM33thlFaok8z2s7a', 'secretary@ceid.upatras.gr', 'secretary', 'Γραμματεία', 'ΤΜΗΥΠ', '2025-05-14 18:31:38', NULL, 1),
(32, 'evoyatzaki', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'evoyatzaki@example.com', 'professor', 'Ελένη', 'Βογιατζάκη', '2025-06-01 09:12:45', NULL, 1),
(33, 'gpapadopoulos', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'gpapadopoulos@example.com', 'professor', 'Γεώργιος', 'Παπαδόπουλος', '2025-06-01 09:12:45', NULL, 1),
(34, 'anikoleris', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'anikoleris@example.com', 'professor', 'Αντώνιος', 'Νικολέρης', '2025-06-01 09:12:45', NULL, 1),
(35, 'mkonstantinou', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'mkonstantinou@example.com', 'professor', 'Μαρία', 'Κωνσταντίνου', '2025-06-01 09:12:45', NULL, 1),
(36, 'kgeorgiou', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'kgeorgiou@example.com', 'professor', 'Κωνσταντίνος', 'Γεωργίου', '2025-06-01 09:12:45', NULL, 1),
(37, 'cpapadimitriou', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'cpapadimitriou@example.com', 'student', 'Χρήστος', 'Παπαδημητρίου', '2025-06-01 09:12:45', NULL, 1),
(38, 'aandreou', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'aandreou@example.com', 'student', 'Αναστασία', 'Ανδρέου', '2025-06-01 09:12:45', NULL, 1),
(39, 'ndimopoulos', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'ndimopoulos@example.com', 'student', 'Νικόλαος', 'Δημόπουλος', '2025-06-01 09:12:45', NULL, 1),
(40, 'evasileiou', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'evasileiou@example.com', 'student', 'Ευαγγελία', 'Βασιλείου', '2025-06-01 09:12:45', NULL, 1),
(41, 'pstavrou', '$2y$10$GNvZZEV9xpS4X7yvXk/Xz.gu2kGOtB7cBRJZWjsUTvmVRZYVUiv0W', 'pstavrou@example.com', 'student', 'Παναγιώτης', 'Σταύρου', '2025-06-01 09:12:45', NULL, 1);

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `committee_invitations`
--
ALTER TABLE `committee_invitations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thesis_assignment_id` (`thesis_assignment_id`),
  ADD KEY `idx_inviting_professor_id` (`inviting_professor_id`),
  ADD KEY `idx_invited_professor_id` (`invited_professor_id`);

--
-- Ευρετήρια για πίνακα `committee_members`
--
ALTER TABLE `committee_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thesis_assignment_id` (`thesis_assignment_id`,`professor_id`),
  ADD KEY `idx_committee_members_professor` (`professor_id`);

--
-- Ευρετήρια για πίνακα `examination_protocols`
--
ALTER TABLE `examination_protocols`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thesis_assignment_id` (`thesis_assignment_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_examination_protocols_date` (`protocol_date`);

--
-- Ευρετήρια για πίνακα `gs_documents`
--
ALTER TABLE `gs_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gs_number` (`gs_number`,`gs_year`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_gs_documents_year` (`gs_year`);

--
-- Ευρετήρια για πίνακα `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Ευρετήρια για πίνακα `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Ευρετήρια για πίνακα `professor_notes`
--
ALTER TABLE `professor_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `idx_professor_notes_assignment` (`thesis_assignment_id`);

--
-- Ευρετήρια για πίνακα `public_presentation_announcements`
--
ALTER TABLE `public_presentation_announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thesis_assignment_id` (`thesis_assignment_id`);

--
-- Ευρετήρια για πίνακα `status_changes`
--
ALTER TABLE `status_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_status_changes_assignment` (`thesis_assignment_id`);

--
-- Ευρετήρια για πίνακα `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`);

--
-- Ευρετήρια για πίνακα `thesis_assignments`
--
ALTER TABLE `thesis_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`status`),
  ADD KEY `thesis_topic_id` (`thesis_topic_id`),
  ADD KEY `idx_thesis_assignments_status` (`status`),
  ADD KEY `idx_thesis_assignments_student` (`student_id`),
  ADD KEY `idx_thesis_assignments_supervisor` (`supervisor_id`);

--
-- Ευρετήρια για πίνακα `thesis_external_links`
--
ALTER TABLE `thesis_external_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thesis_assignment_id` (`thesis_assignment_id`);

--
-- Ευρετήρια για πίνακα `thesis_files`
--
ALTER TABLE `thesis_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thesis_files_assignment` (`thesis_assignment_id`,`file_type`);

--
-- Ευρετήρια για πίνακα `thesis_grades`
--
ALTER TABLE `thesis_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thesis_assignment_id` (`thesis_assignment_id`,`professor_id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `idx_thesis_grades_assignment` (`thesis_assignment_id`);

--
-- Ευρετήρια για πίνακα `thesis_presentations`
--
ALTER TABLE `thesis_presentations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thesis_assignment_id` (`thesis_assignment_id`);

--
-- Ευρετήρια για πίνακα `thesis_topics`
--
ALTER TABLE `thesis_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_thesis_topics_professor` (`professor_id`),
  ADD KEY `idx_thesis_topics_assigned` (`is_assigned`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `committee_invitations`
--
ALTER TABLE `committee_invitations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `committee_members`
--
ALTER TABLE `committee_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT για πίνακα `examination_protocols`
--
ALTER TABLE `examination_protocols`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT για πίνακα `gs_documents`
--
ALTER TABLE `gs_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `professors`
--
ALTER TABLE `professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT για πίνακα `professor_notes`
--
ALTER TABLE `professor_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `public_presentation_announcements`
--
ALTER TABLE `public_presentation_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT για πίνακα `status_changes`
--
ALTER TABLE `status_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT για πίνακα `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT για πίνακα `thesis_assignments`
--
ALTER TABLE `thesis_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT για πίνακα `thesis_external_links`
--
ALTER TABLE `thesis_external_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `thesis_files`
--
ALTER TABLE `thesis_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT για πίνακα `thesis_grades`
--
ALTER TABLE `thesis_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT για πίνακα `thesis_presentations`
--
ALTER TABLE `thesis_presentations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT για πίνακα `thesis_topics`
--
ALTER TABLE `thesis_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `committee_invitations`
--
ALTER TABLE `committee_invitations`
  ADD CONSTRAINT `fk_inv_invited_professor` FOREIGN KEY (`invited_professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_inviting_professor` FOREIGN KEY (`inviting_professor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inv_thesis_assignment` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Περιορισμοί για πίνακα `committee_members`
--
ALTER TABLE `committee_members`
  ADD CONSTRAINT `committee_members_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `committee_members_ibfk_2` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `examination_protocols`
--
ALTER TABLE `examination_protocols`
  ADD CONSTRAINT `examination_protocols_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `examination_protocols_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Περιορισμοί για πίνακα `gs_documents`
--
ALTER TABLE `gs_documents`
  ADD CONSTRAINT `gs_documents_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Περιορισμοί για πίνακα `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `professors`
--
ALTER TABLE `professors`
  ADD CONSTRAINT `professors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `professor_notes`
--
ALTER TABLE `professor_notes`
  ADD CONSTRAINT `professor_notes_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `professor_notes_ibfk_2` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `public_presentation_announcements`
--
ALTER TABLE `public_presentation_announcements`
  ADD CONSTRAINT `public_presentation_announcements_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `status_changes`
--
ALTER TABLE `status_changes`
  ADD CONSTRAINT `status_changes_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_changes_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Περιορισμοί για πίνακα `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `thesis_assignments`
--
ALTER TABLE `thesis_assignments`
  ADD CONSTRAINT `thesis_assignments_ibfk_1` FOREIGN KEY (`thesis_topic_id`) REFERENCES `thesis_topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `thesis_assignments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `thesis_assignments_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `professors` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `thesis_external_links`
--
ALTER TABLE `thesis_external_links`
  ADD CONSTRAINT `thesis_external_links_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `thesis_files`
--
ALTER TABLE `thesis_files`
  ADD CONSTRAINT `thesis_files_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `thesis_grades`
--
ALTER TABLE `thesis_grades`
  ADD CONSTRAINT `thesis_grades_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `thesis_grades_ibfk_2` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `thesis_presentations`
--
ALTER TABLE `thesis_presentations`
  ADD CONSTRAINT `thesis_presentations_ibfk_1` FOREIGN KEY (`thesis_assignment_id`) REFERENCES `thesis_assignments` (`id`) ON DELETE CASCADE;

--
-- Περιορισμοί για πίνακα `thesis_topics`
--
ALTER TABLE `thesis_topics`
  ADD CONSTRAINT `thesis_topics_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
