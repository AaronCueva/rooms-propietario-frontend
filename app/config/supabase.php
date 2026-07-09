<?php
/**
 * Configuración Supabase — Portal Propietario.
 * Mismo proyecto que el portal inquilino (el chat es transversal).
 *
 * La `anon key` es publishable (va en el navegador).
 * La password de postgres NO va aquí; solo en el backend (App\Core\Database).
 */
defined('SUPABASE_URL')      or define('SUPABASE_URL', 'https://lokjiueialuwrulybgut.supabase.co');
defined('SUPABASE_ANON_KEY') or define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imxva2ppdWVpYWx1d3J1bHliZ3V0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODAyODE1MjcsImV4cCI6MjA5NTg1NzUyN30.z5tcJAOMvqe-d8jJs6zC0eRbUPw4wnU23EO9yTrSnd8');
