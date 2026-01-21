document.addEventListener("DOMContentLoaded", function() {
    const nextBtn = document.getElementById("nextBtn");
    const loginBtn = document.getElementById("loginBtn");
    const step1 = document.getElementById("step1");
    const step2 = document.getElementById("step2");
    const unameInput = document.getElementById("uname");
    const secretInput = document.getElementById("secret");
    const status = document.getElementById("status");

    nextBtn.addEventListener("click", async function() {
        const login = unameInput.value.trim();
        if(login === "") {
            alert("Proszę wpisać login.");
            return;
        }

        // Pobranie wyzwania PGP z backendu
        try {
            const res = await fetch(`challenge.php?login=${login}`);
            const data = await res.json();

            if(data.error) {
                status.textContent = data.error;
                status.style.color = "red";
            } else {
                step1.style.display = "none";
                step2.style.display = "block";
                secretInput.focus();
                status.textContent = "Odszyfruj wiadomość i wklej ją poniżej.";
                status.style.color = "green";
            }
        } catch(e) {
            status.textContent = "Błąd połączenia z serwerem";
            status.style.color = "red";
        }
    });

    loginBtn.addEventListener("click", async function() {
        const login = unameInput.value.trim();
        const secret = secretInput.value.trim();

        if(secret === "") {
            alert("Wklej odszyfrowaną wiadomość.");
            return;
        }

        try {
            const res = await fetch("verify.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({login, secret})
            });
            const data = await res.json();

            if(data.success) {
                window.location.href = "home.php";
            } else {
                status.textContent = data.error || "Niepoprawny sekret";
                status.style.color = "red";
            }
        } catch(e) {
            status.textContent = "Błąd połączenia z serwerem";
            status.style.color = "red";
        }
    });
});