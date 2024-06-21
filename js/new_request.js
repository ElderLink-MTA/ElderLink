
/**
 * Select a street by city in Israel
 * Cities data is from https://data.gov.il/dataset/citiesandsettelments
 * Streets data is from https://data.gov.il/dataset/321
 * API documentation https://docs.ckan.org/en/latest/maintaining/datastore.html#ckanext.datastore.logic.action.datastore_search
 */

// Rest API URL
const api_url = "https://data.gov.il/api/3/action/datastore_search";
// Cities endpoint
const cities_resource_id = "5c78e9fa-c2e2-4771-93ff-7f400a12f7ba";
// Streets endpoint
const streets_resource_id = "a7296d1a-f8c9-4b70-96c2-6ebb4352f8e3";
// Field names
const city_name_key = "שם_ישוב";
const street_name_key = "שם_רחוב";
// dataset ids
const cities_data_id = "cities-data";
const streets_data_id = "streets-data";
// input elements
const cities_input = document.getElementById("city-choice");
const streets_input = document.getElementById("street-choice");

const getData = (resource_id, q = "", limit = "100") => {
  return axios.get(api_url, {
    params: { resource_id, q, limit },
    responseType: "json"
  });
};


const parseResponse = (records = [], field_name) => {
    const parsed = records
      .map((record) => {
        const value = record[field_name].trim();
        const boldValue = value.replace(/(.)/g, '<b>$1</b>');
        return `<option value="${value}">${boldValue}</option>`;
      })
      .join('\n') || '';
  
    return Promise.resolve(parsed);
  };

/**
 * Fetch data, parse, and populate Datalist
 */
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

// APP

/**
 * Populate cities.
 * There are about 1300 cities in Israel, and the API upper limit is 32000
 * so we can safely populate the list only once.
 */
populateDataList(
  cities_data_id,
  cities_resource_id,
  city_name_key,
  undefined,
  32000
);

/**
 * Populate streets
 * Update the streets list on every city name change
 * (assuming there aren't more than 32,000 streets in any city)
 */
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
