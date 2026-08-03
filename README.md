# 🤖 Robot Control Pad

A simple web-based control pad for driving a robot remotely. A browser page sends movement commands (forward / backward / left / right / stop) to a PHP + MySQL backend, and the robot (e.g. an ESP32/Arduino) polls that backend to read the latest command and act on it.

## 🎥 Demo

See [`demo.mp4`](./demo.mp4) for a walkthrough of the control pad in action.

## 🧩 How It Works

1. The user opens `index.html` in a browser and taps a direction button.
2. The page sends a `POST` request to `update_command.php` with the button name (e.g. `forward`).
3. `update_command.php` maps the button name to a single-character code and updates the one row (`id = 1`) in the `robot_state` table in MySQL.
4. The robot (or any other client) polls `get_state.php`, which returns the current `command` and `updated_at` timestamp as JSON.
5. The robot reads that character and moves accordingly, until a new command overwrites it.

```
[Browser Control Pad] --POST--> [update_command.php] --UPDATE--> [MySQL: robot_state]
                                                                        |
[Robot / ESP32]        <--GET/poll-- [get_state.php] <--SELECT--------+
```

## 📁 Project Structure

| File | Purpose |
|---|---|
| `index.html` | The control pad UI (forward / left / stop / right / backward buttons). Sends commands via `fetch()`. |
| `update_command.php` | Receives the pressed button name, converts it to a stored code, and updates the database. |
| `get_state.php` | Returns the current command and last-updated timestamp as JSON — this is what the robot polls. |
| `db.php` | Holds the database connection (host, user, password, database name) used by the other PHP scripts. |
| `setup.sql` | One-time SQL script to create the `robot_state` table and seed its single row. |
| `demo.mp4` | Video demo of the control pad working end-to-end. |

## 🗃️ Database Schema

```sql
CREATE TABLE robot_state (
    id INT PRIMARY KEY,
    command CHAR(1) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO robot_state (id, command) VALUES (1, 'S');
```

Only one row is ever used (`id = 1`) — it's continuously overwritten rather than growing a log of every command, which keeps polling fast and simple.

### Command Codes

| Button | Stored Code |
|---|---|
| forward | `f` |
| backward | `b` |
| left | `l` |
| right | `r` |
| stop | `S` |

## 🚀 Setup

1. **Create the database** on your MySQL host (e.g. InfinityFree, or any MySQL/MariaDB host) and run `setup.sql` once in phpMyAdmin's SQL tab.
2. **Configure the connection** in `db.php` with your host, username, password, and database name.
3. **Upload** `index.html`, `update_command.php`, `get_state.php`, and `db.php` to your PHP hosting (same folder).
4. **Open `index.html`** in a browser to control the robot.
5. **Point your robot's firmware** at `get_state.php` and poll it periodically (e.g. every 200–500 ms) to read the current command letter and drive the motors accordingly.

## 🔌 API Reference

### `POST update_command.php`
Updates the stored command.

**Body (form-encoded):** `command=forward` (one of `forward`, `backward`, `left`, `right`, `stop`)

**Response:**
```json
{ "status": "success", "button": "forward", "stored_as": "f" }
```

### `GET get_state.php`
Returns the current command.

**Response:**
```json
{ "command": "f", "updated_at": "2026-08-03 13:45:00" }
```

## ⚠️ Security Notes

- `db.php` currently contains real, plaintext database credentials. Before sharing this project publicly (e.g. pushing to a public GitHub repo), **remove or rotate the credentials** and load them from an untracked config file or environment variable instead.
- `update_command.php` accepts any client — there's no authentication. For anything beyond a personal hobby project, consider adding a shared secret/token check so random visitors can't hijack control of the robot.
- The database uses a prepared statement for the update (good practice, already in place).

## 🛠️ Possible Improvements

- Add a simple auth token to the endpoints.
- Add HTTPS enforcement if the host supports it.
- Add a "last seen" indicator on the UI using the `updated_at` field from `get_state.php`.
- Add keyboard controls (arrow keys) alongside the on-screen buttons.
