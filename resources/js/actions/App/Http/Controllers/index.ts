import DashboardController from './DashboardController'
import WnaController from './WnaController'
import AbgController from './AbgController'
import AffidavitController from './AffidavitController'
import EarlyWarningWnaController from './EarlyWarningWnaController'
import EarlyWarningAbgController from './EarlyWarningAbgController'
import PermohonanController from './PermohonanController'
import LaporanIzinTinggalController from './LaporanIzinTinggalController'
import PenerbitanController from './PenerbitanController'
import RealisasiPnbpController from './RealisasiPnbpController'
import DataUserController from './DataUserController'
import SettingController from './SettingController'
import Teams from './Teams'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    WnaController: Object.assign(WnaController, WnaController),
    AbgController: Object.assign(AbgController, AbgController),
    AffidavitController: Object.assign(AffidavitController, AffidavitController),
    EarlyWarningWnaController: Object.assign(EarlyWarningWnaController, EarlyWarningWnaController),
    EarlyWarningAbgController: Object.assign(EarlyWarningAbgController, EarlyWarningAbgController),
    PermohonanController: Object.assign(PermohonanController, PermohonanController),
    LaporanIzinTinggalController: Object.assign(LaporanIzinTinggalController, LaporanIzinTinggalController),
    PenerbitanController: Object.assign(PenerbitanController, PenerbitanController),
    RealisasiPnbpController: Object.assign(RealisasiPnbpController, RealisasiPnbpController),
    DataUserController: Object.assign(DataUserController, DataUserController),
    SettingController: Object.assign(SettingController, SettingController),
    Teams: Object.assign(Teams, Teams),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers