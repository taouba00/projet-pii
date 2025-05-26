<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link rel="stylesheet" href="creat.css">
</head>
<body>
    <div class="container">
        <h1>Create Your Account</h1>
        
        <div id="error-messages" class="alert alert-danger" style="display: none;">
            <ul id="error-list"></ul>
        </div>

        <form id="signup-form" action="create_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-group floating-label">
                <input type="email" name="email" id="email" class="form-control" placeholder=" " required>
                <label for="email">Email Address</label>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <div class="password-strength">
                    <div class="password-strength-bar"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>
            
            <div class="form-group floating-label">
                <input type="text" name="nom" id="nom" class="form-control" placeholder=" " required>
                <label for="nom">Last Name</label>
            </div>
            
            <div class="form-group floating-label">
                <input type="text" name="prenom" id="prenom" class="form-control" placeholder=" " required>
                <label for="prenom">First Name</label>
            </div>
            
            <div class="form-group">
                <label>I am a:</label>
                <div class="role-toggle">
                    <input type="radio" name="role" id="role-client" value="client" checked>
                    <label for="role-client">Client</label>
                    <input type="radio" name="role" id="role-freelancer" value="freelancer">
                    <label for="role-freelancer">Freelancer</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="photo">Profile Photo</label>
                <div class="file-upload">
                    <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                    <div class="file-upload-label">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                        </svg>
                        <span>Choose an image or drag it here</span>
                    </div>
                    <div class="file-name"></div>
                </div>
            </div>
            
            <button type="submit" class="btn-primary">Create Account</button>
        </form>
    </div>

    <script src="create.js"></script>
</body>
</html>
