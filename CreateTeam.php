<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Team</title>
    <link rel="stylesheet" href="./css/CreateTeam.css">
    <style>
        /* Style the modal background */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        /* Style the modal content */
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        /* Style for the close button */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        /* Style for the error message */
        .modal-content p {
            font-size: 18px;
            color: #F57C00;
            margin: 0;
        }

        .modal-content .error-message {
            color: #121212;
            font-weight: bold;
        }
        
        li {
            color: #F57C00;
        }

        /* Button to update teams */
        #updateTeamsButton {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #updateTeamsButton:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <p class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
    <a href="profile-cms.php">Profile Settings</a>
    <a href="stats-cms.php">Statistics Settings</a>
    <a href="gamresult.php">Game Results</a>
    <a href="CreateTeam.php">Create Team</a>
    <a href="edit-card-content.php">Dashboard Showcase</a>
    <a href="viewteams.php">View Teams</a>
    <a href="Feedback.php">Feedback</a>
    <button class="logout-button" onclick="logout()">Logout</button>
</div>

<div class="container">
    <h2>Create a Team and Add Players</h2>
    <form method="post" action="create_team.php" enctype="multipart/form-data" onsubmit="handleSubmit(event)">
        <div class="form-group">
            <label for="team">Team Name:</label>
            <input type="text" id="team" name="team" required>
        </div>
        <div class="form-group">
            <label for="team_logo">Team Logo:</label>
            <input type="file" id="team_logo" name="team_logo" accept="image/*" required>
        </div>

        <div id="player-container">
            <div class="form-group">
                <label>Player 1:</label>
                <input type="text" name="name[]" placeholder="Player Name" required>
                <input type="number" name="number[]" placeholder="Number" required>
                <label>Position:</label>
                <select name="position[]" required>
                    <option value="Point Guard">Point Guard</option>
                    <option value="Shooting Guard">Shooting Guard</option>
                    <option value="Small Forward">Small Forward</option>
                    <option value="Power Forward">Power Forward</option>
                    <option value="Center">Center</option>
                </select>
                <label>Height:</label>
                <select name="height[]" required>
                    <?php for ($feet = 4; $feet <= 7; $feet++): ?>
                        <?php for ($inches = 0; $inches < 12; $inches++): ?>
                            <option value="<?= $feet . 'ft ' . $inches . 'in' ?>">
                                <?= $feet . 'ft ' . $inches . 'in' ?>
                            </option>
                        <?php endfor; ?>
                    <?php endfor; ?>
                </select>
                <label>Birthdate:</label>
                <input type="date" name="born[]" placeholder="Birthdate" required>
                <label>Profile Picture:</label>
                <input type="file" name="profile_picture[]" accept="image/*">
            </div>
        </div>

        <button type="button" class="add-column-btn" onclick="addColumn()">Add Player</button>
        <input type="submit" value="Create Team">
    </form>
</div>

<div id="modal" class="modal">
    <div class="modal-content" id="modal-content">
        <button id="updateTeamsButton" onclick="updateTeams()">Update Players Teams</button>
    </div>
</div>

<script>
    function addColumn() {
        const container = document.getElementById('player-container');
        const playerCount = container.children.length + 1;

        const playerGroup = document.createElement('div');
        playerGroup.className = 'form-group';

        const label = document.createElement('label');
        label.textContent = 'Player ' + playerCount + ':';
        playerGroup.appendChild(label);

        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.name = 'name[]';
        nameInput.placeholder = 'Player Name';
        nameInput.required = true;
        playerGroup.appendChild(nameInput);

        const numberInput = document.createElement('input');
        numberInput.type = 'number';
        numberInput.name = 'number[]';
        numberInput.placeholder = 'Number';
        numberInput.required = true;
        playerGroup.appendChild(numberInput);

        const positionLabel = document.createElement('label');
        positionLabel.textContent = 'Position:';
        playerGroup.appendChild(positionLabel);
        const positionSelect = document.createElement('select');
        positionSelect.name = 'position[]';
        positionSelect.required = true;
        const positions = ['Point Guard', 'Shooting Guard', 'Small Forward', 'Power Forward', 'Center'];
        positions.forEach(position => {
            const option = document.createElement('option');
            option.value = position;
            option.text = position;
            positionSelect.appendChild(option);
        });
        playerGroup.appendChild(positionSelect);

        const heightLabel = document.createElement('label');
        heightLabel.textContent = 'Height:';
        playerGroup.appendChild(heightLabel);
        const heightSelect = document.createElement('select');
        heightSelect.name = 'height[]';
        heightSelect.required = true;
        for (let feet = 4; feet <= 7; feet++) {
            for (let inches = 0; inches < 12; inches++) {
                const height = `${feet}ft ${inches}in`;
                const option = document.createElement('option');
                option.value = height;
                option.text = height;
                heightSelect.appendChild(option);
            }
        }
        playerGroup.appendChild(heightSelect);

        const bornLabel = document.createElement('label');
        bornLabel.textContent = 'Birthdate:';
        playerGroup.appendChild(bornLabel);
        const bornInput = document.createElement('input');
        bornInput.type = 'date';
        bornInput.name = 'born[]';
        bornInput.placeholder = 'Birthdate';
        bornInput.required = true;
        playerGroup.appendChild(bornInput);

        const profilePictureLabel = document.createElement('label');
        profilePictureLabel.textContent = 'Profile Picture:';
        playerGroup.appendChild(profilePictureLabel);
        const profilePictureInput = document.createElement('input');
        profilePictureInput.type = 'file';
        profilePictureInput.name = 'profile_picture[]';
        profilePictureInput.accept = 'image/*';
        playerGroup.appendChild(profilePictureInput);

        container.appendChild(playerGroup);
    }

    function showModal(message) {
        const modal = document.getElementById('modal');
        const modalContent = document.getElementById('modal-content');

        let content = '';

        try {
            const parsedMessage = JSON.parse(message);

            if (parsedMessage.status === 'exists') {
                content += `<p class="error-message">${parsedMessage.message}</p>`;
                content += `<ul>`;
                for (const [player, number] of Object.entries(parsedMessage.existing_players)) {
                    content += `<li>${player} (Number: ${number})</li>`;
                }
                content += `</ul>`;
            } else {
                content += `<p>${parsedMessage.message}</p>`;
            }
        } catch {
            content = `<p>${message}</p>`;
        }

        modalContent.innerHTML = '<span class="close" onclick="closeModal()">&times;</span>' + content;
        modalContent.innerHTML += '<button id="updateTeamsButton" onclick="updateTeams()">Update Teams</button>';

        modal.style.display = 'block';
    }

    function closeModal() {
        const modal = document.getElementById('modal');
        modal.style.display = 'none';
    }

    function updateTeams() {
        fetch('update_teams.php', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                let content = '';
                if (data.status === 'success') {
                    content = `<p>${data.message}</p>`;
                    if (data.updated_players && data.updated_players.length > 0) {
                        content += '<ul>';
                        data.updated_players.forEach(player => {
                            content += `<li>${player}</li>`;
                        });
                        content += '</ul>';
                    }
                } else if (data.status === 'error') {
                    content = `<p>An error occurred: ${data.message}</p>`;
                } else if (data.status === 'no_players') {
                    content = `<p>${data.message}</p>`;
                }

                document.getElementById('modal-content').innerHTML = '<span class="close" onclick="closeModal()">&times;</span>' + content;
            })
            .catch(error => {
                document.getElementById('modal-content').innerHTML = '<span class="close" onclick="closeModal()">&times;</span><p>An error occurred while updating teams.</p>';
            });
    }

    function handleSubmit(event) {
        event.preventDefault();

        const formData = new FormData(event.target);

        fetch('create_team.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            showModal(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
</body>
</html>
