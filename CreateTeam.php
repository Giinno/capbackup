<!DOCTYPE html>
<html>
<head>
    <title>Create Team</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 80%;
            max-width: 800px;
            overflow-y: auto;
            max-height: 100vh;
        }
        h2 {
            color: #F57C00;
            text-align: center;
        }
        form {
            display: grid;
            gap: 10px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input[type="text"], input[type="date"], input[type="number"], input[type="file"], select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: none;
            border-radius: 4px;
            background-color: #121212;
            color: #ffffff;
        }
        select {
            background-color: #222222;
            color: #ffffff;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            background-color: #F57C00;
            color: #ffffff;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background-color: #e06b00;
        }
        .add-column-btn {
            background-color: #F57C00;
            color: #ffffff;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        .add-column-btn:hover {
            background-color: #e06b00;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #1e1e1e;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            color: #ffffff;
            text-align: center;
            position: relative;
        }
        .close {
            color: #aaaaaa;
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: #ffffff;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
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

            const bornInput = document.createElement('input');
            bornInput.type = 'date';
            bornInput.name = 'born[]';
            bornInput.placeholder = 'Birthdate';
            bornInput.required = true;
            playerGroup.appendChild(bornInput);

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
            modalContent.innerHTML = message;
            modal.style.display = "block";
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            modal.style.display = "none";
            window.location.reload();
        }

        async function handleSubmit(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData
                });

                if (response.ok) {
                    const message = await response.text();
                    showModal(message);
                } else {
                    showModal("Failed to create team. Please try again.");
                }
            } catch (error) {
                showModal("Error occurred. Please try again.");
            }
        }
    </script>
</head>
<body>
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
                    <select name="position[]" required>
                        <option value="Point Guard">Point Guard</option>
                        <option value="Shooting Guard">Shooting Guard</option>
                        <option value="Small Forward">Small Forward</option>
                        <option value="Power Forward">Power Forward</option>
                        <option value="Center">Center</option>
                    </select>
                    <select name="height[]" required>
                        <?php for ($feet = 4; $feet <= 7; $feet++): ?>
                            <?php for ($inches = 0; $inches < 12; $inches++): ?>
                                <option value="<?= $feet . 'ft ' . $inches . 'in' ?>">
                                    <?= $feet . 'ft ' . $inches . 'in' ?>
                                </option>
                            <?php endfor; ?>
                        <?php endfor; ?>
                    </select>
                    <input type="date" name="born[]" placeholder="Birthdate" required>
                    <input type="file" name="profile_picture[]" accept="image/*">
                </div>
            </div>

            <button type="button" class="add-column-btn" onclick="addColumn()">Add Player</button>
            <input type="submit" value="Create Team">
        </form>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content" id="modal-content"></div>
        <span class="close" onclick="closeModal()">&times;</span>
    </div>
</body>
</html>
