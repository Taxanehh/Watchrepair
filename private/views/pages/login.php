<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Enhanced session security
session_regenerate_id(true);
ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 1); // Uncomment if using HTTPS

require_once 'db.php';

function findUserByEmail($email) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Redirect if logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: home");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    $user = findUserByEmail($email);

    if ($user) {
        // Hybrid check for plaintext or hashed passwords
        if ($password === $user['password'] || password_verify($password, $user['password'])) {
            // Migrate plaintext password to hash if needed
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                // Update database with new hash
                $conn = getDbConnection();
                $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$newHash, $user['id']]);
            }

            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            
            // Regenerate session ID on login
            session_regenerate_id(true);
            
            header("Location: home");
            exit;
        } else {
            $error = "Onjuist wachtwoord.";
        }
    } else {
        $error = "Gebruiker niet gevonden.";
    }
}
// ... rest of your HTML remains the same ...


?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <title>Horlogic | Inloggen</title>
  <link rel="stylesheet" type="text/css" href="/css/globals.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    /* Full-page background */
    body {
      margin: 0;
      padding: 0;
      font-family: 'Open Sans', sans-serif;
      background:
        linear-gradient(
          135deg,
          rgba(46, 52, 81, 0.4),
          rgba(52, 40, 104, 0.95)
        ),
        url("/../../../img/login-bg-2.jpg") 
          no-repeat center center fixed;
      background-size: cover;
      background-blend-mode: normal;
    }

    /* Centered white panel */
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      width: 320px;
      margin: 5% auto;
      padding: 2rem;
      border-radius: 4px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
      height: 525px;
    }

    .login-container input[type="text"],
    .login-container input[type="email"],
    .login-container input[type="password"] {
      color: #666; /* a darker gray for typed text */
      }

    .logo img {
      max-height: 60px;
      margin-bottom: 1rem;
    }

    .form-group {
      margin-bottom: 1rem;
      text-align: left;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
    }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .checkbox-group {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 3rem;
      position: relative;
      bottom: -10px;
    }

    .checkbox-group input[type="checkbox"] {
        margin: 0;           /* Remove extra default margin */
        }
        .checkbox-group label {
        margin: 0;           /* Ensure no extra top/bottom margin offsets the alignment */
        }

    button {
      background-color: #194978;
      color: #fff;
      border: none;
      padding: 0.55rem 1.2rem;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8rem;
      font-weight: 100;
    }
    button:hover {
      background-color: #153c63;
    }

    .error {
      color: red;
      margin-bottom: 1rem;
    }

    .forgot-password {
      margin-top: 4rem;
      display: inline-block;
      font-size: 0.9rem;
      text-decoration: none;
      color: #555;
    }
    .forgot-password:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <style>
      html, body {
        margin: 0;
        padding: 0;
        font-family: 'Open Sans', sans-serif;
        font-size: 1rem;
        line-height: 1.5;
        font-weight: 300;
        }
    </style>
    <div class="logo">
      <!-- Replace with your actual logo image if desired -->
      <img src="/../../../img/logo.png" alt="Weisz Group Logo" style="width: 100px; height: 115.083px;"  />
    </div>

    <?php if (!empty($error)): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="/login">
      <div class="form-group">
        <label for="email">E-mailadres <span style="color:red">*</span></label>
        <input 
          type="email" 
          name="email" 
          id="email"
          placeholder="E-mailadres" 
          required
        >
      </div>

      <div class="form-group">
        <label for="password">Wachtwoord <span style="color:red">*</span></label>
        <input 
          type="password" 
          name="password" 
          id="password"
          placeholder="Wachtwoord" 
          required
        >
      </div>

      <div class="checkbox-group">
        <input type="checkbox" id="remember" name="remember" />
        <label for="remember" style="font-weight: normal;">Ingelogd blijven</label>
      </div>

      <button type="submit">Inloggen <i class="fa fa-sign-in"></i></button>

    </form>

    <a class="forgot-password" href="#">Wachtwoord vergeten?</a>
  </div>

</body>
</html>
