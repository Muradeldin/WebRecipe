# The Flavor Forge

A recipe management web application built with PHP, MySQL, and JavaScript. Allows users to create, manage, and explore recipes through an easy-to-use interface.

Website: https://the-flavor-forge.byethost22.com/

## Features

- Digital Recipe Book - Create and store recipes with detailed information
- Recipe Discovery - Browse and view all available recipes
- Add Recipes - Add new recipes with difficulty levels and instructions
- Recipe Calculator - Calculate recipe portions and ingredient quantities
- Team Page - Information about the creators
- Responsive Design - Works on different screen sizes

## Project Structure

```
index.html
recipes.php
add-recipe.php
recipe-calculator.php
recipe_view.php
team.html

css/
  style.css
  recipe_creation.css
  stylepersonal.css
  styleteam.css

js/
  main.js
  create_recipe.js
  recipe-calculator.js

images/
  recipe_img/
  recipes/
  video/

php_db/
  db.php
  store-recipe.php
  delete_recipe.php
```

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Server**: Web server with PHP support

## Getting Started

### Prerequisites

- Web server with PHP support (Apache, Nginx, etc.)
- MySQL database
- Modern web browser

### Installation

1. **Clone or download** the project files to your web server's document root
2. **Update database credentials** in `php_db/db.php`:
   ```php
   $DB_HOST = "your_host";
   $DB_NAME = "your_database";
   $DB_USER = "your_user";
   $DB_PASS = "your_password";
   ```
3. **Create database tables** following the schema used by the application
4. **Access** the application through your web server

### Database Setup

The application expects a MySQL database with tables for:
- `recipes` - Recipe information
- `difficulties` - Difficulty levels for recipes
- Recipe-related metadata (ingredients, instructions, etc.)

## Core Functionality

### Home Page (index.html)
- Welcome section with call-to-action
- Feature highlights
- User-generated content section

### Recipes (recipes.php)
- Display all available recipes
- Filter and search capabilities
- Recipe preview cards

### Add Recipe (add-recipe.php)
- Form to create new recipes
- Select difficulty levels
- Upload recipe images
- Add ingredients and instructions

### Recipe Calculator (recipe-calculator.php)
- Adjust recipe portions
- Automatic ingredient quantity calculations
- Easy ingredient scaling

### Team Page (team.html)
- Team member profiles (Murad, Adham, Natali)
- Team contribution details

## File Descriptions

### PHP Backend
- **db.php** - PDO database connection and configuration
- **store-recipe.php** - API endpoint for saving new recipes
- **delete-recipe.php** - API endpoint for removing recipes

### JavaScript
- **main.js** - Primary application logic and utilities
- **create_recipe.js** - Form handling for recipe creation
- **recipe-calculator.js** - Calculation logic for recipe scaling

### CSS
- **style.css** - Global styles and layout
- **recipe_creation.css** - Recipe form styling
- **stylepersonal.css** - Personal/individual page styles
- **styleteam.css** - Team page specific styling

## Usage

1. **Browse Recipes**: Visit the home page and click "Start Cooking" or navigate to "All Recipes"
2. **Add a Recipe**: Click "Add Recipe" and fill out the form with recipe details
3. **Calculate Portions**: Use the Recipe Calculator to adjust ingredient quantities
4. **View Details**: Click on any recipe to see full instructions and ingredients
5. **Learn About Us**: Visit the Team page to meet the creators

## Development Team

- **Murad**
- **Adham**
- **Natali**

This project was developed as part of a Software Engineering Workshop.
This project contains database credentials in php_db/db.php.


