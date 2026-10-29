<?php
/**
 * Funkcje pomocnicze dla aplikacji CineHub
 * Rola: Backend Developer & Data Analyst
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Bezpieczne wypisanie tekstu w widoku HTML (Ochrona przed XSS)
 *
 * @param string|null $value
 * @return string
 */
function e(?string $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Ustawia komunikat błyskowy w sesji
 *
 * @param string $type 'success' | 'error' | 'info'
 * @param string $message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Pobiera i usuwa komunikat błyskowy z sesji
 *
 * @return array|null
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Formatowanie oceny (np. 8.5 / 10)
 *
 * @param float|null $rating
 * @return string
 */
function formatRating(?float $rating): string {
    if ($rating === null || $rating <= 0) {
        return 'Brak ocen';
    }
    return number_format($rating, 1, '.', '') . ' / 10';
}

