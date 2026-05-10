<?php

return [

  // Layout / Navigation
  'nav_header'          => 'Navigation',
  'home'                => 'Home',
  'dashboard'           => 'Dashboard',
  'my_case_logs'        => 'My Case Logs',
  'log_new_case'        => 'Log New Case',
  'pending_approvals'   => 'Pending Approvals',
  'director_dashboard'  => 'Director Dashboard',
  'logout'              => 'Logout',

  // Login
  'welcome'             => 'Welcome to STMS',
  'sign_in_subtitle'    => 'Sign in to the Surgical Training Management System.',
  'email'               => 'Email',
  'password'            => 'Password',
  'sign_in'             => 'Sign in',
  'seeded_hint'         => 'Use seeded users after running migrations and seeders.',

  // Stat card labels
  'submitted_cases'     => 'Submitted Cases',
  'approved'            => 'Approved',
  'pending'             => 'Pending',
  'rejected'            => 'Rejected',
  'pending_reviews'     => 'Pending Reviews',
  'approved_cases'      => 'Approved Cases',
  'rejected_cases'      => 'Rejected Cases',
  'residents'           => 'Residents',
  'procedures'          => 'Procedures',
  'pending_cases'       => 'Pending Cases',

  // Charts / section headings
  'my_submissions_chart'       => 'My Submissions (6 Months)',
  'my_progress_by_procedure'   => 'My Progress By Procedure',
  'my_decisions_chart'         => 'My Decisions (6 Months)',
  'pending_approvals_snapshot' => 'Pending Approvals Snapshot',
  'approved_trend_chart'       => 'Approved Cases Trend (6 Months)',
  'status_mix_chart'           => 'Resident Progress Status Mix',
  'director_section_title'     => 'Training Director Dashboard',

  // Table columns
  'col_procedure'       => 'Procedure',
  'col_completed'       => 'Completed',
  'col_expected'        => 'Expected',
  'col_progress'        => 'Progress',
  'col_status'          => 'Status',
  'col_date'            => 'Date',
  'col_case_code'       => 'Case Code',
  'col_role'            => 'Role',
  'col_feedback'        => 'Feedback',
  'col_resident'        => 'Resident',
  'col_action'          => 'Action',
  'col_year'            => 'Year',

  // Empty states
  'no_progress_data'    => 'No progress data.',
  'no_case_logs'        => 'No case logs yet.',
  'no_pending_approvals' => 'No pending approvals.',
  'no_progress_rows'    => 'No progress rows available.',

  // Case log form
  'log_case_title'      => 'Log Operation Case',
  'anonymized_hint'     => 'Use anonymized case codes only. Do not enter patient-identifiable data.',
  'case_code'           => 'Case Code',
  'select_procedure'    => 'Select procedure',
  'operation_type'      => 'Operation Type',
  'elective'            => 'Elective',
  'emergency'           => 'Emergency',
  'difficulty_level'    => 'Difficulty Level (1-5)',
  'role_in_operation'   => 'Role in Operation',
  'assistant'           => 'Assistant',
  'first_assistant'     => 'First Assistant',
  'primary_surgeon'     => 'Primary Surgeon',
  'supervised_primary'  => 'Supervised Primary Surgeon',
  'supervisor'          => 'Supervisor',
  'auto_assign'         => 'Auto-assign first supervisor',
  'operation_date'      => 'Operation Date',
  'note'                => 'Note',
  'submit_for_approval' => 'Submit For Approval',
  'new_log'             => '+ New Log',

  // Approvals
  'pending_case_approvals' => 'Pending Case Approvals',
  'approve'             => 'Approve',
  'reject'              => 'Reject',
  'feedback_optional'   => 'Feedback (optional)',
  'save'                => 'Save',

  // Director
  'status_color_hint'      => 'Color logic: green = on track, yellow = at risk, red = behind.',
  'upcoming_procedure'     => 'Upcoming Procedure',
  'generate_recommendation' => 'Generate Recommendation',

  // Flash messages
  'flash_case_submitted'   => 'Case log submitted for approval.',
  'flash_review_saved'     => 'Case review saved.',
  'flash_no_resident_data' => 'No resident data available for recommendation.',

  // Language switcher
  'language'            => 'Language',
];
