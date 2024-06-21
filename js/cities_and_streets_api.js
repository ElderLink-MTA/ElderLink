// Select a street by city in Israel

const api_url = "https://data.gov.il/api/3/action/datastore_search";
const cities_resource_id = "5c78e9fa-c2e2-4771-93ff-7f400a12f7ba";
const streets_resource_id = "a7296d1a-f8c9-4b70-96c2-6ebb4352f8e3";
const city_name_key = "שם_ישוב";
const street_name_key = "שם_רחוב";
const cities_data_id = "cities-data";
const streets_data_id = "streets-data";
const cities_input = document.getElementById("city-choice");
const streets_input = document.getElementById("street-choice");

const getData = (resource_id, q = "", limit = "100") => {
  //console.log("sending", resource_id, query);
  return axios.get(api_url, {
    params: { resource_id, q, limit },
    responseType: "json"
  });
};

const parseResponse = (records = [], field_name) => {
  const parsed =
    records
      .map((record) => `<option value="${record[field_name].trim()}">`)
      .join("\n") || "";
  //console.log("parsed", field_name, parsed);
  return Promise.resolve(parsed);
};

const populateDataList = (id, resource_id, field_name, query, limit) => {
  const datalist_element = document.getElementById(id);
  if (!datalist_element) {
    console.log(
      "Datalist with id",
      id,
      "doesn't exist in the document, aborting"
    );
    return;
  }
  getData(resource_id, query, limit)
    .then((response) =>
      parseResponse(response?.data?.result?.records, field_name)
    )
    .then((html) => (datalist_element.innerHTML = html))
    .catch((error) => {
      console.log("Couldn't get list for", id, "query:", query, error);
    });
};


populateDataList(
  cities_data_id,
  cities_resource_id,
  city_name_key,
  undefined,
  32000
);

// Populate streets

cities_input.addEventListener("change", (event) => {
  populateDataList(
    streets_data_id,
    streets_resource_id,
    street_name_key,
    {
      שם_ישוב: cities_input.value
    },
    32000
  );
});
