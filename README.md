# PGP Two-Step Login System (PHP)

A custom authentication system written in PHP that uses **PGP public-key cryptography** as a second authentication factor.  
Instead of passwords, users must **decrypt a server-generated challenge** using their private PGP key.

This approach eliminates password storage and provides strong cryptographic authentication.

---

## 🔐 How It Works

1. User enters their **username**
2. Server:
   - verifies the user exists
   - retrieves the user's **PGP public key**
   - generates a random challenge
3. The challenge is:
   - sent to the browser
   - encrypted client-side using OpenPGP.js
4. User:
   - decrypts the message locally using their **private PGP key**
   - pastes the decrypted value back into the form
5. Server verifies the decrypted response
6. On success:
   - user session is created
   - access is granted to the protected area

No passwords are transmitted or stored.

---

## 🧱 Architecture Overview

### Frontend
- Pure HTML + JavaScript
- OpenPGP.js used for client-side encryption
- No frameworks

### Backend
- PHP (procedural)
- MySQL (mysqli)
- Session-based authentication

---
