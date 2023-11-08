<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <h1>Instructor Account Credential Information</h1>
<div class="text-center mt-3 mb-3">
  <p >Welcome {{ $name }} !</p>
</div>
    
    <p>Username : {{ $username }}</p>
    <p>Password : {{ $password }}</p>
    <div class="footer">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td class="content-block powered-by">
              Powered by <a href="https://finworld.com/">Finworld</a>.
            </td>
          </tr>
        </table>
      </div>
</body>
</html>