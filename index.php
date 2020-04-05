<?php include("includes/init.php");
$title = "Course Catalog";

$db = open_sqlite_db('secure/catalog.sqlite');
$messages = array();

//user-defined function to print the records
function printrec($rec)
{
?>
  <tr>
    <td><?php echo htmlspecialchars($rec["id"]); ?></td>
    <td><?php echo htmlspecialchars($rec["cname"]); ?></td>
    <td><?php echo htmlspecialchars($rec["numcredits"]); ?></td>
    <td><?php echo htmlspecialchars($rec["req"]); ?></td>
    <td><?php echo htmlspecialchars($rec["descriptions"]); ?></td>

  </tr>
<?php

}

const SEARCH_F = [
  "all" => "Search Everything",
  "id" => "Search By Course ID",
  "cname" => "Search By Course Name",
  "req" => "Search By Requirement Fulfilled",
];
if (isset($_GET['search'])) {
  $dosearch = TRUE;

  $cat = filter_input(INPUT_GET, 'cat', FILTER_SANITIZE_STRING);
  if (in_array($cat, array_keys(SEARCH_F))) {
    $search_f = $cat;
  } else {
    array_push($messages, "* Invalid category for search.");
    $dosearch = FALSE;
  }
  $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
  $search = trim($search);
} else {
  // No search provided, so set the product to query to NULL
  $dosearch = FALSE;
  $cat = NULL;
  $search = NULL;
}

//insert form
$courses = exec_sql_query($db, "SELECT DISTINCT req FROM courses", NULL)->fetchAll(PDO::FETCH_COLUMN);

if (isset($_POST["submit_insert"])) {


  $req = filter_input(INPUT_POST, 'req', FILTER_SANITIZE_STRING);
  $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
  $cname = filter_input(INPUT_POST, 'cname', FILTER_SANITIZE_STRING);
  $numcredits = filter_input(INPUT_POST, 'numcredits', FILTER_VALIDATE_INT);
  $descriptions = filter_input(INPUT_POST, 'descriptions', FILTER_SANITIZE_STRING);

  // product name required
  $checkarray = array($id,$cname,$descriptions);
  if (anynull($checkarray)){
    $invalid_review = TRUE;
  }
  if ($numcredits < 1 || $numcredits > 4) {
    $invalid_review = TRUE;
  }

  if (!in_array($req, $courses)) {
    $invalid_review = TRUE;
  }


  // insert valid reviews into database
  if ($invalid_review) {
    array_push($messages, "* Failed to add course. Invalid course or failed to complete course form.");
  } else {
    $sql = "INSERT INTO courses (id, cname, numcredits, req, descriptions) VALUES (:id, :cname, :numcredits, :req, :descriptions)";
    $params = array(
      ':id' => $id,
      ':cname' => $cname,
      ':numcredits' => $numcredits,
      ':req' => $req,
      ':descriptions' => $descriptions
    );
    try {
      $result = exec_sql_query($db, $sql, $params);
      array_push($messages, "* Your course has been recorded. Thank you for your contribution");
    } catch (Exception $e) {
      array_push($messages, "* Failed to add course. Please check for existing courses.");
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Course Catalog for Information Science, Systems, and Technology major</title>
  <link rel="stylesheet" type="text/css" href="styles/site.css" media="all" />
</head>

<a href="index.php">
  <header>
    <h1 class="title">Course Catalog for Information Science, Systems, and Technology major </h1>
  </header>
</a>

<body>
  <main>
    <h2><?php echo $title; ?></h2>
    <p>Welcome to the course catalog for ISST (Information, Science, Systems, and Technology) majors.</p>

    <?php
    foreach ($messages as $message) {
      echo "<p><strong>" . htmlspecialchars($message) . "</strong></p>\n";
    }
    ?>


    <form id="searchForm" action="index.php" method="get" novalidate>
      <select name="cat">
        <?php foreach (SEARCH_F as $field => $label) { ?>
          <option value="<?php echo $field; ?>"><?php echo $label; ?></option>
        <?php } ?>
      </select>
      <input type="text" name="search" required />
      <button type="submit">Search</button>
    </form>

    <?php
    if ($dosearch) { // We have a specific shoe to query!
    ?>
      <h2>Search Results</h2>

      <?php
      if ($search_f == "all") {
        $sql = "SELECT * FROM courses WHERE cname LIKE '%$search%' OR numcredits LIKE '%$search%' OR id LIKE '%$search%' OR descriptions LIKE '%$search%' OR req  LIKE '%$search%';";
      } else {
        $sql = "SELECT * FROM courses WHERE " . $search_f . " LIKE '%' || :search || '%'";
        $params = array(
          ':search' => $search
        );
      }
    } else {
      // No shoe to query, so return everything!
      // Hint: You don't need to change any of this code.
      ?>
      <h2>All Courses</h2>
      <?php

      $sql = "SELECT * FROM courses";
      $params = array();
    }

    $result = exec_sql_query($db, $sql, $params);
    if ($result) {
      $recs = $result->fetchAll();

      if (count($recs) > 0) {
        // We have records to display
      ?>
        <table>
          <tr>
            <th>Course ID</th>
            <th>Course Name</th>
            <th># of Credits</th>
            <th>Requirement Fulfilled</th>
            <th>Description</th>
          </tr>

          <?php
          foreach ($recs as $rec) {
            printrec($rec);
          }
          ?>
        </table>
    <?php
      } else {
        // No results found
        echo "<p>No matching course found.</p>";
      }
    }
    ?>


    <?php
    ?>
    <div class="form">
      <h2>Fill out this form to add a course to the catalog:</h2>
      <form id="courseform" method="post" action="index.php" novalidate>
        <div>
          <label for="id_field">Course ID:</label>
          <input id="id_field" type="text" name="id" required />
        </div>
        <div>
          <label for="name_field">Course Name:</label>
          <input type="text" id="name_field" name="cname" required />
        </div>
        <div>
          <label for="cred_field"># of Credits:</label>
          <input type="number" id="cred_field" name="numcredits" min=1 max=4 required />
        </div>
        <div>

          <label>Requirement fulfilled:</label>
          <select name="req" required>
            <option value="" selected disabled>Choose requirement</option>
            <?php
            foreach ($courses as $course) {
              echo "<option value=\"" . htmlspecialchars($course) . "\">" . htmlspecialchars($course) . "</option>";
            }
            ?>
          </select>
        </div>


        <div class="comment">
          <label for="description_field">Description:</label>
          <textarea name="descriptions" cols="40" rows="5" required></textarea>
        </div>

        <div class="button">
          <button name="submit_insert" type="submit">Add Course</button>
        </div>

      </form>
    </div>
  </main>


</body>

<footer>
  Please visit the <a href="https://infosci.cornell.edu/undergraduate/info-sci-majors/bs-information-science-systems-and-technology/degree-requirements">Office Cornell website</a> for more information.
</footer>

</html>
