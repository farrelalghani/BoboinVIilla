import { createBrowserRouter } from "react-router";
import SearchResults from "./pages/SearchResults";
import VillaDetail from "./pages/VillaDetail";

export const router = createBrowserRouter([
  {
    path: "/",
    Component: SearchResults,
  },
  {
    path: "/villa/:id",
    Component: VillaDetail,
  },
]);
