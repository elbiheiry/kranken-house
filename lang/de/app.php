<?php

return [

  // Layout / Navigation
  'nav_header'          => 'Navigation',
  'home'                => 'Startseite',
  'dashboard'           => 'Dashboard',
  'my_case_logs'        => 'Meine Falldaten',
  'log_new_case'        => 'Neuen Fall eintragen',
  'pending_approvals'   => 'Ausstehende Genehmigungen',
  'director_dashboard'  => 'Direktorenpanel',
  'logout'              => 'Abmelden',

  // Login
  'welcome'             => 'Willkommen bei STMS',
  'sign_in_subtitle'    => 'Melden Sie sich beim Chirurgischen Ausbildungsmanagementsystem an.',
  'email'               => 'E-Mail',
  'password'            => 'Passwort',
  'sign_in'             => 'Anmelden',
  'seeded_hint'         => 'Verwenden Sie die geseedeten Benutzer nach dem Ausführen der Migrationen und Seeders.',

  // Stat card labels
  'submitted_cases'     => 'Eingereichte Fälle',
  'approved'            => 'Genehmigt',
  'pending'             => 'Ausstehend',
  'rejected'            => 'Abgelehnt',
  'pending_reviews'     => 'Ausstehende Überprüfungen',
  'approved_cases'      => 'Genehmigte Fälle',
  'rejected_cases'      => 'Abgelehnte Fälle',
  'residents'           => 'Assistenzärzte',
  'procedures'          => 'Eingriffe',
  'pending_cases'       => 'Ausstehende Fälle',

  // Charts / section headings
  'my_submissions_chart'       => 'Meine Einreichungen (6 Monate)',
  'my_progress_by_procedure'   => 'Mein Fortschritt nach Eingriff',
  'my_decisions_chart'         => 'Meine Entscheidungen (6 Monate)',
  'pending_approvals_snapshot' => 'Ausstehende Genehmigungen – Übersicht',
  'approved_trend_chart'       => 'Trend genehmigter Fälle (6 Monate)',
  'status_mix_chart'           => 'Fortschrittsstatus der Assistenzärzte',
  'director_section_title'     => 'Direktorenpanel',

  // Table columns
  'col_procedure'       => 'Eingriff',
  'col_completed'       => 'Abgeschlossen',
  'col_expected'        => 'Erwartet',
  'col_progress'        => 'Fortschritt',
  'col_status'          => 'Status',
  'col_date'            => 'Datum',
  'col_case_code'       => 'Fallcode',
  'col_role'            => 'Rolle',
  'col_feedback'        => 'Rückmeldung',
  'col_resident'        => 'Assistenzarzt',
  'col_action'          => 'Aktion',
  'col_year'            => 'Jahr',

  // Empty states
  'no_progress_data'    => 'Keine Fortschrittsdaten vorhanden.',
  'no_case_logs'        => 'Noch keine Falldaten.',
  'no_pending_approvals' => 'Keine ausstehenden Genehmigungen.',
  'no_progress_rows'    => 'Keine Fortschrittszeilen verfügbar.',

  // Case log form
  'log_case_title'      => 'Operationsfall eintragen',
  'anonymized_hint'     => 'Verwenden Sie ausschließlich anonymisierte Fallcodes. Geben Sie keine patientenidentifizierenden Daten ein.',
  'case_code'           => 'Fallcode',
  'select_procedure'    => 'Eingriff auswählen',
  'operation_type'      => 'Operationstyp',
  'elective'            => 'Elektiv',
  'emergency'           => 'Notfall',
  'difficulty_level'    => 'Schwierigkeitsgrad (1–5)',
  'role_in_operation'   => 'Rolle bei der Operation',
  'assistant'           => 'Assistent',
  'first_assistant'     => 'Erster Assistent',
  'primary_surgeon'     => 'Hauptoperateur',
  'supervised_primary'  => 'Beaufsichtigter Hauptoperateur',
  'supervisor'          => 'Betreuer',
  'auto_assign'         => 'Ersten Betreuer automatisch zuweisen',
  'operation_date'      => 'Operationsdatum',
  'note'                => 'Anmerkung',
  'submit_for_approval' => 'Zur Genehmigung einreichen',
  'new_log'             => '+ Neuer Eintrag',

  // Approvals
  'pending_case_approvals' => 'Ausstehende Fallgenehmigungen',
  'approve'             => 'Genehmigen',
  'reject'              => 'Ablehnen',
  'feedback_optional'   => 'Rückmeldung (optional)',
  'save'                => 'Speichern',

  // Director
  'status_color_hint'      => 'Farblogik: grün = auf Kurs, gelb = gefährdet, rot = zurück.',
  'upcoming_procedure'     => 'Bevorstehender Eingriff',
  'generate_recommendation' => 'Empfehlung generieren',

  // Flash messages
  'flash_case_submitted'   => 'Fallprotokoll zur Genehmigung eingereicht.',
  'flash_review_saved'     => 'Fallprüfung gespeichert.',
  'flash_no_resident_data' => 'Keine Assistenzarztdaten für die Empfehlung verfügbar.',

  // Language switcher
  'language'            => 'Sprache',
];
