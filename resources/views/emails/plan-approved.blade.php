<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial;
            color: #333;
        }

        .box {
            padding: 20px;
            background: #f7f4f8;
        }
    </style>
</head>

<body>
    <div class="box">
        <h1 style="color: #d62828;">Workout Plan Approved!</h1>
        <p>Your plan from <strong>{{ $plan->trainer->name }}</strong> is ready.</p>
        <p>Reference: {{ $plan->reference_number }}</p>
    </div>
</body>

</html>