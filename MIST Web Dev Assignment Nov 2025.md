# **MIST Web Dev Assignment: Custom Elementor Plugin – “Featured Resource Block”**

**Please create a small WordPress plugin that includes the following components below.**

---

## **1\. Custom Post Type: “Resources”**

Fields (via standard WP UI):

* Title  
* Excerpt  
* Featured Image  
* Custom field: “Resource URL” (ACF or native meta is fine)

---

## **2\. Elementor Widget: “Featured Resource Block”**

The widget should allow the user to configure:

* Selected Resource (dropdown populated from CPT)  
* Layout style (Card / Minimal)  
* Button text (string)  
* Gradient background toggle (on/off)  
* Image size selection

**The front-end output should be clean, responsive, and professionally structured.**

---

## **3\. Plugin Settings Page**

Add a settings page at:

**Settings → Resource Sync**

Fields:

* API Key (text field)  
* “Enable Sync” toggle

---

## **4\. Mock API Sync**

When “Enable Sync” is ON, your plugin should:

* Run a cron event every 15 minutes  
* Fetch data from this endpoint:  
  [https://mocki.io/v1/0c7b33d3-2996-4d7f-a009-4ef34a27c7e9](https://mocki.io/v1/0c7b33d3-2996-4d7f-a009-4ef34a27c7e9)  
* Create **new** Resource posts if they don’t exist  
* Update existing Resources if they do  
* Cache API results for 5 minutes using transients  
* Handle failures gracefully (no errors shown to end users)

---

## **5\. Documentation**

Please include a brief Markdown README describing:

* Installation steps  
* How the Elementor widget works  
* How the sync works  
* Any known limitations  
* What you’d improve with more time

---

# **Video Walkthrough (up to 5 minutes)**

A short screen-recorded walkthrough helps us confirm authorship and understand your thinking.

What to show:

* Your plugin folder structure  
* Key classes or functions  
* How the Elementor widget is organized  
* How the API sync is implemented

**Important:** This should be a “show and tell” of your code – NOT a demo of your final UI. 

---

# **Submission Instructions**

Please reply to this email with:

1. **A link to your GitHub or GitLab repository** containing the plugin  
   (preferred over sending ZIP files)  
2. A short note summarizing your approach  
3. Your 3–5 minute walkthrough video link (Loom, etc.)

---

# **Evaluation Criteria**

We will review your submission based on:

* Code structure & organization  
* Correct use of WordPress hooks and best practices  
* Elementor widget development quality  
* Responsiveness & front-end execution  
* API integration, error handling & caching  
* Security & sanitization awareness  
* Documentation and explanation clarity

**This assignment is not about perfection — it’s about understanding your approach and engineering habits.**

