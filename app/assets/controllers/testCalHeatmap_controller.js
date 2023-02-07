import CalHeatmap from "cal-heatmap"
import { Tooltip } from 'cal-heatmap'

const cal = new CalHeatmap();
cal.paint({
  date: {
    start: new Date('2020-09-01'),
    min: new Date('2020-09-01'),
    max: new Date('2023-08-31')

  },
  domain: {
    type: 'month'
  },
}, [
  [ Tooltip, { enabled: true, text: (timestamp, value, dayjsDate) => `${timestamp} ${value} ${dayjsDate.toLocalString()}` } ]
]);