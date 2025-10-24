<?php
session_start();
include('../includes/dbconnection.php');

$showOtpModal = false;
$generateOtp = "";

if (isset($_POST['login'])) {
    $adminusername = $_POST['username'];
    $pass = $_POST['password'];


    $query = "SELECT * FROM systemadmin WHERE username = $1";
    $result = pg_query_params($fsms_conn, $query, array($adminusername));

    if (pg_num_rows($result) > 0) {

        $num = pg_fetch_assoc($result);


        if (password_verify($pass, $num['password'])) {
            $generateOtp = rand(100000, 999999);
            $_SESSION['login'] = $_POST['username'];
            $_SESSION['adminid'] = $num['id'];

            pg_query_params($fsms_conn, "UPDATE systemadmin SET otp = $1 WHERE id = $2", array($generateOtp, $num['id']));
            // $extra = "dashboard.php";
            // // echo "<script>window.location.href='" . $extra . "'</script>";
            // // exit();
            $showOtpModal = true;
        } else {

            echo "<script>alert('Invalid username or password');</script>";
            $extra = "index.php";
            echo "<script>window.location.href='" . $extra . "'</script>";
            exit();
        }
    } else {

        echo "<script>alert('Invalid username or password');</script>";
        $extra = "index.php";
        echo "<script>window.location.href='" . $extra . "'</script>";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $enteredOtp = $_POST['otp'];
    $adminusername = $_SESSION['login'];

    $res = pg_query_params($fsms_conn, "SELECT otp FROM systemadmin WHERE username=$1", array($adminusername));
    $row = pg_fetch_assoc($res);

    if ($row && $enteredOtp == $row['otp']) {
        echo "<script> window.location.href='dashboard.php';</script>";
        exit;
    } else {
        echo "<script>alert('Incorrect OTP');</script>";
    }
}
?>





<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <title>System Admin Login Form </title>
    <link rel="shortcut icon" href="../assets/favicon.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html,
        body {
            display: grid;
            height: 100%;
            width: 100%;
            place-items: center;
            background: #f2f2f2;
            /* background: linear-gradient(-135deg, #c850c0, #4158d0); */
        }

        ::selection {
            background: #4158d0;
            color: #fff;
        }

        .wrapper {
            width: 380px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0px 15px 20px rgba(0, 0, 0, 0.1);
        }

        .wrapper .title {
            font-size: 35px;
            font-weight: 600;
            text-align: center;
            line-height: 100px;
            color: #fff;
            user-select: none;
            border-radius: 15px 15px 0 0;
            background: linear-gradient(-135deg, #c850c0, #4158d0);
        }

        .wrapper form {
            padding: 10px 30px 50px 30px;
        }

        .wrapper form .field {
            height: 50px;
            width: 100%;
            margin-top: 20px;
            position: relative;
        }

        .wrapper form .field input {
            height: 100%;
            width: 100%;
            outline: none;
            font-size: 17px;
            padding-left: 20px;
            border: 1px solid lightgrey;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .wrapper form .field input:focus,
        form .field input:valid {
            border-color: #4158d0;
        }

        .wrapper form .field label {
            position: absolute;
            top: 50%;
            left: 20px;
            color: #999999;
            font-weight: 400;
            font-size: 17px;
            pointer-events: none;
            transform: translateY(-50%);
            transition: all 0.3s ease;
        }

        form .field input:focus~label,
        form .field input:valid~label {
            top: 0%;
            font-size: 16px;
            color: #4158d0;
            background: #fff;
            transform: translateY(-50%);
        }


        form .content input {
            width: 15px;
            height: 15px;
            background: red;
        }

        form .content label {
            color: #262626;
            user-select: none;
            padding-left: 5px;
        }

        form .content .pass-link {
            color: "";
        }

        form .field input[type="submit"] {
            color: #fff;
            border: none;
            padding-left: 0;
            margin-top: -10px;
            font-size: 20px;
            font-weight: 500;
            cursor: pointer;
            background: linear-gradient(-135deg, #c850c0, #4158d0);
            transition: all 0.3s ease;
        }

        form .field input[type="submit"]:active {
            transform: scale(0.95);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .otp-input-container {
            display: flex;
            justify-content: center;
            margin: 15px 0;
            gap: 8px;
        }

        .otp-input {
            width: 35px;
            height: 35px;
            text-align: center;
            font-size: 18px;
            color: #333;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .otp-input:focus {
            border-color: #4facfe;
            outline: none;
        }

        #resendBtn {
            text-decoration: none;
            color: #2e52c9;
            font-weight: bold;
            cursor: pointer;
        }

        #resendBtn:hover {
            text-decoration: underline;
        }


        .modal-footer {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
            margin: 20px 0 0 50px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="title">
           System Admin Login
        </div>
        <?php
        if (isset($_SESSION['login_error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['login_error'] . "</div>";
            unset($_SESSION['login_error']);
        }
        ?>

        <form action="index.php" method="post">
            <div class="field">
                <input type="text" name="username" required>
                <label>Username</label>
            </div>
            <div class="field">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>

            <div class="field">
                <input type="submit" name="login" value="Login">
            </div>

        </form>
    </div>

    <div id="otpModal" class="modal">
        <div class="modal-content">
            <h2>OTP Verification</h2>
            <p>Enter the OTP you have received</p>

            <div class="otp-input-container">
                <input type="text" class="otp-input" maxlength="1">
                <input type="text" class="otp-input" maxlength="1">
                <input type="text" class="otp-input" maxlength="1">
                <input type="text" class="otp-input" maxlength="1">
                <input type="text" class="otp-input" maxlength="1">
                <input type="text" class="otp-input" maxlength="1">
            </div>

            <div class="modal-footer">
                <p><a href="#" id="resendBtn">Resend OTP &rarr;</a></p>
                <div id="otpTimer" style="color:blue;"></div>
            </div>
        </div>
    </div>

    <?php if ($showOtpModal): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const modal = document.getElementById("otpModal");
                const otpInputs = document.querySelectorAll(".otp-input");
                const otpTimer = document.getElementById("otpTimer");
                const resendBtn = document.getElementById("resendBtn");

                let generatedOtp = "<?= $generateOtp ?>";
                console.log("Generated OTP:", generatedOtp);


                let timeLeft = 120;
                let timerInterval;


                resendBtn.style.pointerEvents = "none";
                resendBtn.style.color = "gray";
                resendBtn.style.cursor = "default";

                function startTimer() {
                    updateTimerText();
                    resendBtn.style.pointerEvents = "none";
                    resendBtn.style.color = "gray";
                    resendBtn.style.cursor = "default";

                    timerInterval = setInterval(() => {
                        timeLeft--;
                        updateTimerText();

                        if (timeLeft <= 0) {
                            clearInterval(timerInterval);
                            otpTimer.textContent = "OTP expired. Please click resend.";
                            resendBtn.style.pointerEvents = "auto";
                            resendBtn.style.color = "#2e52c9";
                            resendBtn.style.cursor = "pointer";
                        }
                    }, 1000);
                }

                function updateTimerText() {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    otpTimer.textContent = `In ${minutes}:${seconds.toString().padStart(2, '0')} minutes`;
                }


                modal.style.display = "block";
                otpInputs[0].focus();
                startTimer();
                alert("Your OTP is: " + generatedOtp);


                otpInputs.forEach((input, index) => {
                    input.addEventListener("input", () => {
                        if (input.value.length === 1 && index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        }
                        checkOtpComplete();
                    });

                    input.addEventListener("keydown", (e) => {
                        if (e.key === "Backspace" && input.value === "" && index > 0) {
                            otpInputs[index - 1].focus();
                        }
                    });


                    input.addEventListener("paste", (e) => {
                        e.preventDefault();
                        const pasteData = e.clipboardData.getData("text").trim();

                        if (/^\d{6}$/.test(pasteData)) {
                            otpInputs.forEach((box, idx) => {
                                box.value = pasteData.charAt(idx);
                            });
                            checkOtpComplete();
                        }
                    });
                });

                function checkOtpComplete() {
                    const enteredOtp = Array.from(otpInputs).map(input => input.value).join("");
                    if (enteredOtp.length === 6) {
                        const hiddenForm = document.createElement("form");
                        hiddenForm.method = "POST";
                        hiddenForm.style.display = "none";

                        const otpField = document.createElement("input");
                        otpField.type = "hidden";
                        otpField.name = "otp";
                        otpField.value = enteredOtp;

                        hiddenForm.appendChild(otpField);
                        document.body.appendChild(hiddenForm);
                        hiddenForm.submit();
                    }
                }

                resendBtn.addEventListener("click", function (e) {
                    e.preventDefault();
                    if (resendBtn.style.pointerEvents === "none") return;

                    clearInterval(timerInterval);
                    timeLeft = 120

                    0;
                    startTimer();

                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "resend_otp.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            generatedOtp = xhr.responseText.trim();
                            console.log("New OTP:", generatedOtp);
                            alert(`New OTP sent: ${generatedOtp}`);


                            otpInputs[0].focus();
                        }
                    };
                    xhr.send();
                });
            });
        </script>

    <?php endif; ?>
</body>

</html>