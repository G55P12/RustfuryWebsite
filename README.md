## **Main Template Install Instructions**

### 1. Upload Web Files
1. Extract and upload all files from the .zip folder into the root directory of your webserver.
2. Visit your site page and verify that it passes the compatibility tests.
 - Remove compatibility.php from the root if these tests are passed.

### 2. SteamAuth configuration
1. Open steamauth/steamConfig.php file and enter your Steam API Key
   - **Steam API Key**: get yours [here](https://steamcommunity.com/dev/apikey).
2. Leave the remaining fields empty, they will default to the needed site values

### 3. Configure the Admin Account
1. Open `admin/config.php`.
2. Add your SteamID to the `admin` array.
3. In your website’s header, use the **Steam** button to log in.
4. Locate the **Web Panel** link in the site footer
5. If the admin config contains your steam id, the **Confirm Admin Login** button will appear, allowing you to enter the panel
 - The web panel contains settings pages for each major part of the site configuration file.
 - You can use these panels to customize your site information. Make changes and refresh the page to see them apply.
  -- Editing the configuration file directly is still possible for those who would rather stick to that method.

---

## **Main Template Update Instructions**

### 1. Upload Web Files
1. Backup all existing web files before beginning update!
2. After backup, remove all template files from your webserver.
3. Upload all files from the archive into the root directory of your webserver.
4. To restore any images, copy/paste your /img folder from your backup file into the new /assets folder on the webserver.
5. If custom style modifications have been made to your site, compare your backup style.css to the updated style.css and make the appropriate changes.
 - Be sure to note the additional sections that have been added with the update. Alter as needed.
6. Open the config.php from your original backup and copy your site values into the new config.php on the webserver.
 - Optional: You can complete this step using the new admin panels after finishing steps 2 and 3.

### 2. SteamAuth configuration
1. Open steamauth/steamConfig.php file and enter your Steam API Key
   - **Steam API Key**: get yours [here](https://steamcommunity.com/dev/apikey).
2. Leave the remaining fields empty, they will default to the needed site values

### 3. Configure the Admin Account
1. Open `admin/includes/config.php`.
2. Add your SteamID to the `admin` array.
3. In your website’s header, use the **Steam** button to log in.
4. Locate the **Web Panel** link in the site footer
5. If the admin config contains your steam id, the **Confirm Admin Login** button will appear, allowing you to enter the panel
 - The web panel contains settings pages for each major part of the site configuration file.
 - You can use these panels to restore your site information from your backup configuration file.

---

Hey there!

Thanks for purchasing our product. We hope you enjoy using your new leaderboard!

If you do not have an Editor which can highlight code, we would advise you to download Notepad++
(https://notepad-plus-plus.org/downloads/) for better readability in files.

REQUIREMENTS & INSTALLATION INSTRUCTIONS
Can be found on our discord: https://promeus.dev/discord
Or online at: https://promeus.dev

If you have any issues or open questions feel free to contact our team on Discord: https://promeus.dev/discord

If you're happy with the product, we'd appreciate a review from you. It means a lot! https://promeus.dev

- Ralli
